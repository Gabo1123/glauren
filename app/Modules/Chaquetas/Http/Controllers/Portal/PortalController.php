<?php

namespace App\Modules\Chaquetas\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\ContractChange;
use App\Modules\Chaquetas\Models\ContractMessage;
use App\Modules\Chaquetas\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    /** Colegio del usuario autenticado (o 404 si no tiene). */
    private function school()
    {
        $school = Auth::user()->school;
        abort_unless($school, 403, 'Tu cuenta no tiene un colegio asociado.');

        return $school;
    }

    public function dashboard()
    {
        $school = $this->school();
        $contracts = $school->contracts()
            ->with('jacketType')
            ->withCount('students')
            ->orderByDesc('year')
            ->get();

        $ids = $contracts->pluck('id');
        $students = \App\Modules\Chaquetas\Models\Student::whereIn('contract_id', $ids)->get();

        $stats = [
            'contracts'    => $contracts->count(),
            'students'     => $students->count(),
            'paid'         => $students->where('payment_status', 'pagado')->count(),
            'pending_pay'  => $students->filter(fn ($s) => ! in_array($s->payment_status, ['pagado', 'en_revision'], true))->count(),
            'pending_sign' => $contracts->where('contract_status', 'enviado')->count(),
            'unread'       => \App\Modules\Chaquetas\Models\ContractMessage::whereIn('contract_id', $ids)
                ->where('sender', 'glauren')->where('read', false)->count(),
        ];
        $stats['pay_pct'] = $stats['students'] > 0 ? (int) round($stats['paid'] / $stats['students'] * 100) : 0;

        // Fotos recientes del proceso
        $recentPhotos = \App\Modules\Chaquetas\Models\ContractPhoto::whereIn('contract_id', $ids)
            ->latest()->limit(6)->get();

        // Posición en el ranking de colegios (por votos)
        $rank = null;
        $entries = \App\Modules\Chaquetas\Models\RankingEntry::where('active', true)->orderByDesc('votes')->get()->values();
        $pos = $entries->search(fn ($e) => mb_strtolower(trim($e->name)) === mb_strtolower(trim($school->name)));
        if ($pos !== false) {
            $rank = ['pos' => $pos + 1, 'total' => $entries->count(), 'votes' => $entries[$pos]->votes];
        }

        // Días de producción (para fecha estimada)
        $prodDays = (int) \App\Modules\Chaquetas\Models\Setting::get('production_days', 35);

        return view('portal.dashboard', compact('school', 'contracts', 'stats', 'recentPhotos', 'rank', 'prodDays'));
    }

    public function contract(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);

        $contract->load(['jacketType', 'photos', 'students' => fn ($q) => $q->orderBy('name'), 'priceChanges', 'signers']);
        $stages = Contract::STAGES;

        // Resumen de pagos
        $students = $contract->students;
        $pay = [
            'total'     => $students->count(),
            'pagados'   => $students->where('payment_status', 'pagado')->count(),
            'revision'  => $students->where('payment_status', 'en_revision')->count(),
            'pendientes' => $students->filter(fn ($s) => ! in_array($s->payment_status, ['pagado', 'en_revision'], true))->count(),
        ];

        // Diseño aprobado (última cotización que coincide con el colegio)
        $design = Quote::whereNotNull('image_path')
            ->where(fn ($q) => $q->where('school', 'like', '%'.$school->name.'%')->orWhere('jacket_type_id', $contract->jacket_type_id))
            ->latest()->first();

        // Mensajes con Glauren
        $messages = ContractMessage::where('contract_id', $contract->id)->orderBy('id')->get();
        ContractMessage::where('contract_id', $contract->id)->where('sender', 'glauren')->where('read', false)->update(['read' => true]);

        return view('portal.contract', compact('school', 'contract', 'stages', 'pay', 'design', 'messages'));
    }

    /** El colegio aprueba el diseño final. */
    public function approveDesign(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        $contract->update(['design_approved_at' => now()]);

        return back()->with('ok', '¡Diseño aprobado! El equipo procederá con la producción.');
    }

    /** El colegio aprueba o rechaza un cambio que afecta el precio. */
    public function decideChange(Request $request, Contract $contract, ContractChange $change)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id && $change->contract_id === $contract->id, 403);

        $data = $request->validate(['decision' => 'required|in:aprobado,rechazado']);
        $change->update(['status' => $data['decision'], 'decided_at' => now()]);

        return back()->with('ok', $data['decision'] === 'aprobado' ? 'Cambio aprobado.' : 'Cambio rechazado.');
    }

    /** Certificado de entrega (imprimible) para próximos contratos. */
    public function certificate(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        abort_unless($contract->production_status === 'entregado', 403, 'El certificado está disponible al entregar el pedido.');

        $contract->load('school', 'jacketType', 'students', 'priceChanges');

        return view('portal.certificate', compact('contract', 'school'));
    }

    /** Vista imprimible del contrato completo. */
    public function contractDocument(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        $contract->load('school', 'jacketType', 'students', 'signers', 'priceChanges');

        return view('portal.contract-doc', compact('contract', 'school'));
    }

    /** El colegio envía un mensaje al equipo Glauren. */
    public function sendMessage(Request $request, Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);

        $data = $request->validate(['body' => 'required|string|max:1000']);
        ContractMessage::create(['contract_id' => $contract->id, 'sender' => 'colegio', 'body' => $data['body']]);

        return back()->with('ok', 'Mensaje enviado a Glauren.')->withFragment('mensajes');
    }

    /** El colegio agrega un alumno con datos básicos (sin medidas). */
    public function addStudent(Request $request, Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        abort_if($contract->isSigned(), 403, 'El contrato ya está firmado; la lista de alumnos quedó cerrada.');

        $data = $request->validate([
            'name'     => 'required|string|max:160',
            'document' => 'required|string|max:40',
            'gender'   => 'nullable|in:M,F,Otro',
            'nickname' => 'nullable|string|max:60',
        ]);

        $contract->students()->create($data);

        return back()->with('ok', 'Alumno agregado. El equipo Glauren tomará las medidas.');
    }

    public function deleteStudent(Contract $contract, $student)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        abort_if($contract->isSigned(), 403, 'El contrato ya está firmado; la lista de alumnos quedó cerrada.');

        $student = $contract->students()->findOrFail($student);

        // El colegio solo puede borrar alumnos que aún no tienen medidas registradas.
        abort_if($student->hasMeasurements(), 403, 'No puedes eliminar un alumno con medidas ya tomadas.');

        $student->delete();

        return back()->with('ok', 'Alumno eliminado.');
    }
}

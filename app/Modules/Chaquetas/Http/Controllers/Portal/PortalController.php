<?php

namespace App\Modules\Chaquetas\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
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

        return view('portal.dashboard', compact('school', 'contracts'));
    }

    public function contract(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);

        $contract->load(['jacketType', 'photos', 'students' => fn ($q) => $q->orderBy('name')]);
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

        $student = $contract->students()->findOrFail($student);

        // El colegio solo puede borrar alumnos que aún no tienen medidas registradas.
        abort_if($student->hasMeasurements(), 403, 'No puedes eliminar un alumno con medidas ya tomadas.');

        $student->delete();

        return back()->with('ok', 'Alumno eliminado.');
    }
}

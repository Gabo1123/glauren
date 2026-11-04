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

        return back()->with('ok', '¡Diseño aprobado! El equipo procederá con la producción.')->withFragment('diseno');
    }

    /** El colegio aprueba o rechaza un cambio que afecta el precio. */
    public function decideChange(Request $request, Contract $contract, ContractChange $change)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id && $change->contract_id === $contract->id, 403);

        $data = $request->validate(['decision' => 'required|in:aprobado,rechazado']);
        $change->update(['status' => $data['decision'], 'decided_at' => now()]);

        return back()->with('ok', $data['decision'] === 'aprobado' ? 'Cambio aprobado.' : 'Cambio rechazado.')->withFragment('mensajes');
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
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^[\d\s+()-]{7,20}$/'],
        ], ['phone.regex' => 'Escribe un celular válido, solo números.']);

        $data['combo'] = $contract->defaultCombo();
        // La base de datos guarda "hombre" / "mujer"
        $data['gender'] = ['M' => 'hombre', 'F' => 'mujer'][$data['gender'] ?? ''] ?? null;
        $contract->students()->create($data);

        return back()->with('ok', 'Alumno agregado. El equipo Glauren tomará las medidas.')->withFragment('alumnos');
    }

    /** El colegio sube el comprobante del anticipo. */
    public function depositProof(Request $request, Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id && $contract->hasDeposit(), 403);
        $request->validate(['proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:8192'], [
            'proof.required' => 'Adjunta la foto o el PDF del comprobante.',
            'proof.mimes'    => 'El comprobante debe ser una imagen o un PDF.',
        ]);
        $path = $request->file('proof')->store('anticipos/'.$contract->id, 'public');
        $contract->update(['deposit_proof' => $path, 'deposit_declared_at' => now()]);

        // Aviso al administrador
        $to = \App\Modules\Chaquetas\Models\Setting::get('contact_notify_email') ?: config('mail.from.address');
        if ($to) {
            try {
                \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Modules\Chaquetas\Mail\SimpleMail(
                    '💰 '.$school->name.' subió el comprobante del anticipo',
                    'Comprobante de anticipo por revisar',
                    $school->name.' (contrato '.$contract->code.') subió el comprobante del anticipo de $'.number_format((float) $contract->deposit_amount, 0, ',', '.').'. Revísalo y apruébalo en el contrato.',
                    [], 'Abrir contrato', route('admin.contracts.show', $contract).'#anticipo',
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Aviso de anticipo: correo falló', ['msg' => $e->getMessage()]);
            }
        }

        return back()->with('ok', '¡Gracias! Recibimos el comprobante del anticipo. Te confirmamos apenas lo revisemos.');
    }

    /** Formato de Excel para cargar a todo el curso de una vez. */
    public function studentsTemplate(Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);

        $rows = [['Documento', 'Nombre completo', 'Apodo', 'Celular', 'Sexo (M/F)']];
        foreach ($contract->students()->orderBy('name')->get() as $s) {
            $rows[] = [$s->document, $s->name, $s->nickname, $s->phone, $s->gender];
        }
        if (count($rows) === 1) {
            $rows[] = ['1010101010', 'Valentina Ríos Gómez', 'Vale', '3001234567', 'F'];
            $rows[] = ['1020202020', 'Juan Pablo Torres', 'Juancho', '3109876543', 'M'];
        }

        return \App\Modules\Chaquetas\Support\StudentSheet::download('alumnos-'.($contract->code ?: $contract->id).'.csv', $rows);
    }

    /** El colegio sube el Excel con la lista del curso. Solo agrega los que no estén (por documento). */
    public function importStudents(Request $request, Contract $contract)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        abort_if($contract->isSigned(), 403, 'El contrato ya está firmado; la lista de alumnos quedó cerrada.');
        $request->validate(['file' => 'required|file|max:4096'], ['file.required' => 'Elige el archivo de Excel (CSV).']);

        $S = \App\Modules\Chaquetas\Support\StudentSheet::class;
        $rows = $S::read($request->file('file'), 400);
        if (! $rows || ! array_key_exists('documento', $rows[0]) || ! array_key_exists('nombre', $rows[0])) {
            return back()->withErrors(['file' => 'El archivo debe tener las columnas Documento y Nombre. Descarga el formato y vuelve a intentarlo.'])->withFragment('alumnos');
        }

        $created = $repeated = 0;
        $bad = [];
        foreach ($rows as $i => $r) {
            $doc = $S::document($r['documento'] ?? '');
            $name = trim($r['nombre'] ?? '');
            if ($doc === '' && $name === '') {
                continue;
            }
            if (strlen($doc) < 5 || $name === '') {
                $bad[] = $i + 2;
                continue;
            }
            if ($contract->students()->where('document', $doc)->exists()) {
                $repeated++;
                continue;
            }
            $phone = preg_replace('/[^\d+]/', '', (string) ($r['celular'] ?? ''));
            $g = $S::gender($r['sexo'] ?? null);
            $contract->students()->create([
                'document' => $doc,
                'name'     => mb_convert_case(mb_strtolower($name), MB_CASE_TITLE),
                'nickname' => trim($r['apodo'] ?? '') ?: null,
                'phone'    => strlen($phone) >= 7 ? $phone : null,
                'gender'   => $g,
                'combo'    => $contract->defaultCombo(),
            ]);
            $created++;
        }

        $msg = "Listo: $created alumno(s) agregado(s)".($repeated ? ", $repeated ya estaban" : '').'.';
        if ($bad) {
            $msg .= ' Revisa las filas '.implode(', ', array_slice($bad, 0, 10)).': les falta documento o nombre.';
        }

        return back()->with('ok', $msg)->withFragment('alumnos');
    }

    /** El colegio agrega o corrige el celular de un alumno (sirve para recuperar el PIN y recibir avisos). */
    public function updateStudentPhone(Request $request, Contract $contract, $student)
    {
        $school = $this->school();
        abort_unless($contract->school_id === $school->id, 403);
        $student = $contract->students()->whereKey($student)->firstOrFail();

        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s+()-]{7,20}$/'],
        ], ['phone.regex' => 'Escribe un celular válido, solo números.']);
        $student->update(['phone' => $data['phone'] ?: null]);

        return back()->with('ok', 'Celular de '.$student->name.' guardado.')->withFragment('alumnos');
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

        return back()->with('ok', 'Alumno eliminado.')->withFragment('alumnos');
    }
}

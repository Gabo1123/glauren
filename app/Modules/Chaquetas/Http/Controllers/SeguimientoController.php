<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeguimientoController extends Controller
{
    public function index()
    {
        return view('chaquetas.seguimiento.index');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'document' => 'required|string|max:40',
        ]);

        $student = Student::with(['contract.school', 'contract.jacketType', 'contract.photos'])
            ->where('document', trim($data['document']))
            ->latest()
            ->first();

        if (! $student) {
            return back()
                ->withErrors(['document' => 'No encontramos ese documento en ningún contrato. Verifica el número o pregunta a tu colegio.'])
                ->withInput();
        }

        return view('chaquetas.seguimiento.resultado', compact('student'));
    }

    /** El estudiante registra/actualiza su WhatsApp para recibir avisos del pedido. */
    public function savePhone(Request $request)
    {
        $student = Student::with(['contract.school', 'contract.jacketType', 'contract.photos'])
            ->where('id', $request->input('student_id'))
            ->where('document', trim((string) $request->input('document')))
            ->first();

        if (! $student) {
            return redirect()->route('seguimiento')->withErrors(['document' => 'No pudimos validar tus datos. Busca de nuevo por tu documento.']);
        }

        $data = $request->validate([
            'phone' => 'required|string|min:7|max:20',
        ], [
            'phone.required' => 'Escribe tu número de WhatsApp.',
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) <= 10 && ! str_starts_with($phone, '57')) {
            $phone = '57'.$phone; // formato Colombia
        }

        $student->update(['phone' => $phone]);
        $student->refresh();

        return view('chaquetas.seguimiento.resultado', ['student' => $student, 'phoneSaved' => true]);
    }

    /** El estudiante registra su pago (efectivo/Nequi/Bancolombia) → queda pendiente por aprobación. */
    public function declarePayment(Request $request)
    {
        $student = Student::with(['contract.school', 'contract.jacketType', 'contract.photos'])
            ->where('id', $request->input('student_id'))
            ->where('document', trim((string) $request->input('document')))
            ->first();

        if (! $student) {
            return redirect()->route('seguimiento')->withErrors(['document' => 'No pudimos validar tus datos. Busca de nuevo por tu documento.']);
        }

        if ($student->isPaid()) {
            return view('chaquetas.seguimiento.resultado', ['student' => $student]);
        }

        // Solo métodos habilitados globalmente (efectivo apagado por defecto)
        $allowed = array_values(array_filter([
            \App\Modules\Chaquetas\Models\Setting::get('pay_allow_efectivo', '0') === '1' ? 'efectivo' : null,
            \App\Modules\Chaquetas\Models\Setting::get('pay_allow_nequi', '1') === '1' ? 'nequi' : null,
            \App\Modules\Chaquetas\Models\Setting::get('pay_allow_bancolombia', '1') === '1' ? 'bancolombia' : null,
        ]));
        if (empty($allowed)) {
            return view('chaquetas.seguimiento.resultado', ['student' => $student]);
        }

        $rules = [
            'method' => 'required|in:'.implode(',', $allowed),
            'proof'  => 'nullable|image|max:5120',
        ];
        // Comprobante obligatorio en transferencias
        if (in_array($request->input('method'), ['nequi', 'bancolombia'], true)) {
            $rules['proof'] = 'required|image|max:5120';
        }

        $v = Validator::make($request->all(), $rules, [
            'proof.required' => 'Sube la foto del comprobante de tu transferencia.',
            'proof.image'    => 'El comprobante debe ser una imagen (JPG o PNG).',
            'method.required' => 'Elige cómo vas a pagar.',
        ]);

        if ($v->fails()) {
            return view('chaquetas.seguimiento.resultado', [
                'student'       => $student,
                'declareErrors' => $v->errors()->all(),
            ]);
        }

        $update = [
            'payment_status'      => 'en_revision',
            'payment_method'      => $request->input('method'),
            'payment_declared_at' => now(),
        ];
        if ($request->hasFile('proof')) {
            $update['payment_proof'] = $request->file('proof')->store('comprobantes', 'public');
        }
        $student->update($update);
        $student->refresh();

        return view('chaquetas.seguimiento.resultado', ['student' => $student, 'declared' => true]);
    }
}

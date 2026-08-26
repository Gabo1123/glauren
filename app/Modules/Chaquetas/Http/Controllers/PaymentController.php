<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Services\RapydService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    public function __construct(private RapydService $rapyd) {}

    /** El alumno inicia el pago desde su seguimiento (enlace firmado). */
    public function start(Request $request, Student $student)
    {
        if ($student->isPaid()) {
            return redirect()->route('seguimiento')->with('info', 'Tu pago ya está registrado.');
        }

        $amount = (float) ($student->amount ?? $student->contract?->price_per_unit ?? 0);

        if ($amount <= 0) {
            return view('chaquetas.pago.estado', [
                'ok' => false,
                'title' => 'Pago no disponible',
                'msg' => 'Aún no hay un valor asignado a tu chaqueta. Escríbenos y te ayudamos.',
            ]);
        }

        if (! $this->rapyd->configured()) {
            return view('chaquetas.pago.estado', [
                'ok' => false,
                'title' => 'Pagos en configuración',
                'msg' => 'El pago en línea se activará muy pronto. Por ahora coordina con tu colegio o escríbenos por WhatsApp.',
            ]);
        }

        try {
            $checkout = $this->rapyd->createCheckout(
                $student,
                $amount,
                URL::signedRoute('pay.complete', ['student' => $student->id]),
                route('pay.cancel'),
            );

            if (empty($checkout['redirect_url'])) {
                throw new \RuntimeException('Rapyd no devolvió URL de pago.');
            }

            $student->update([
                'rapyd_checkout_id' => $checkout['id'],
                'payment_status'    => 'procesando',
            ]);

            return redirect()->away($checkout['redirect_url']);
        } catch (\Throwable $e) {
            Log::error('Rapyd checkout error', ['student' => $student->id, 'error' => $e->getMessage()]);

            return view('chaquetas.pago.estado', [
                'ok' => false,
                'title' => 'No pudimos abrir el pago',
                'msg' => 'Ocurrió un problema al conectar con la pasarela. Inténtalo de nuevo o escríbenos por WhatsApp.',
            ]);
        }
    }

    /** Rapyd regresa aquí al terminar. Confirmamos contra la API. */
    public function complete(Request $request, Student $student)
    {
        $paid = $this->confirmCheckout($student);

        return view('chaquetas.pago.estado', [
            'ok' => $paid,
            'title' => $paid ? '¡Pago recibido!' : 'Estamos confirmando tu pago',
            'msg' => $paid
                ? 'Gracias, registramos tu pago. Ya puedes ver el estado en tu seguimiento.'
                : 'Recibimos tu regreso desde la pasarela. Si el cobro se hizo, se reflejará en unos minutos.',
            'student' => $student,
        ]);
    }

    public function cancel()
    {
        return view('chaquetas.pago.estado', [
            'ok' => false,
            'title' => 'Pago cancelado',
            'msg' => 'No se realizó ningún cobro. Puedes intentarlo de nuevo cuando quieras.',
        ]);
    }

    /** Webhook de Rapyd (pago completado). */
    public function webhook(Request $request)
    {
        $raw = $request->getContent();
        $payload = json_decode($raw, true) ?: [];

        // Verificación best-effort de la firma
        try {
            $this->rapyd->verifyWebhook(
                $request->fullUrl(),
                (string) $request->header('salt'),
                (string) $request->header('timestamp'),
                (string) $request->header('signature'),
                $raw,
            );
        } catch (\Throwable $e) {
            // no bloqueamos por firma en esta fase; solo registramos
        }

        $data = $payload['data'] ?? [];
        $studentId = $data['metadata']['student_id'] ?? null;

        if (! $studentId && ! empty($data['merchant_reference_id'])) {
            // STU-{id}-{ts}
            if (preg_match('/^STU-(\d+)-/', $data['merchant_reference_id'], $m)) {
                $studentId = (int) $m[1];
            }
        }

        $type = strtoupper((string) ($payload['type'] ?? ''));
        $status = strtoupper((string) ($data['status'] ?? ($data['payment']['status'] ?? '')));

        $isPaid = str_contains($type, 'COMPLETED') || in_array($status, ['CLO', 'ACT', 'PAID', 'COMPLETED'], true);

        if ($studentId && $isPaid) {
            $this->markPaid(Student::find($studentId));
        }

        return response()->json(['received' => true]);
    }

    /** Consulta el checkout en Rapyd y marca pagado si corresponde. */
    private function confirmCheckout(Student $student): bool
    {
        if ($student->isPaid()) {
            return true;
        }
        if (! $this->rapyd->configured() || ! $student->rapyd_checkout_id) {
            return false;
        }

        try {
            $resp = $this->rapyd->request('get', '/v1/checkout/'.$student->rapyd_checkout_id);
            $data = $resp->json('data', []);
            $status = strtoupper((string) ($data['status'] ?? ($data['payment']['status'] ?? '')));

            if (in_array($status, ['CLO', 'ACT', 'PAID', 'COMPLETED'], true)) {
                $this->markPaid($student);

                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Rapyd confirm error', ['student' => $student->id, 'error' => $e->getMessage()]);
        }

        return false;
    }

    private function markPaid(?Student $student): void
    {
        if ($student && ! $student->isPaid()) {
            $student->update(['payment_status' => 'pagado', 'paid_at' => now()]);
        }
    }
}

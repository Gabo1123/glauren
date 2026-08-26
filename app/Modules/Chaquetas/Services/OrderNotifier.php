<?php

namespace App\Modules\Chaquetas\Services;

use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Models\WaMessage;

/**
 * Avisos automáticos por WhatsApp (la IA "avisa sola").
 * Solo escribe a estudiantes que tengan teléfono vinculado.
 */
class OrderNotifier
{
    public function __construct(private WhatsAppService $wa) {}

    public function enabled(): bool
    {
        return Setting::get('wa_notify_stage', '1') === '1' && $this->wa->configured();
    }

    /** Avisa a los estudiantes del contrato que su pedido cambió de etapa. */
    public function notifyStage(Contract $contract): int
    {
        if (! $this->enabled()) {
            return 0;
        }

        $stage = $contract->stageLabel();
        $prenda = $contract->jacketType?->name ?? 'prenda';
        $base = rtrim(config('app.url', 'https://chaquetasdeprom.com'), '/');
        $sent = 0;

        foreach ($contract->students()->whereNotNull('phone')->where('notify_optin', true)->get() as $s) {
            if ($s->notified_stage === $contract->production_status) {
                continue; // ya avisado de esta etapa
            }

            $msg = $this->stageMessage($s, $prenda, $stage, $contract->production_status, $base);

            // Recordatorio de abono final: si aún no ha pagado y ya está cerca de la entrega
            if (! $s->isPaid() && in_array($contract->production_status, ['control_calidad', 'listo'], true)) {
                $msg .= "\n\n💰 Recuerda dejar tu pago listo para la entrega. Puedes pagar o subir tu comprobante buscando tu documento en {$base}/mi-chaqueta";
            }

            $res = $this->wa->sendText($s->phone, $msg);

            WaMessage::create([
                'phone' => $s->phone, 'lead_id' => null,
                'direction' => 'out',
                'body' => $msg.($res['ok'] ? '' : ' (no enviado)'),
            ]);

            $s->update(['notified_stage' => $contract->production_status]);
            $sent++;
        }

        return $sent;
    }

    /** Recordatorio de abono final a los estudiantes que aún deben (a pedido del admin). */
    public function remindPayment(Contract $contract): int
    {
        if (! $this->wa->configured()) {
            return 0;
        }

        $base = rtrim(config('app.url', 'https://chaquetasdeprom.com'), '/');
        $prenda = $contract->jacketType?->name ?? 'prenda';
        $sent = 0;

        $students = $contract->students()
            ->whereNotNull('phone')->where('notify_optin', true)
            ->where('payment_status', '!=', 'pagado')->get();

        foreach ($students as $s) {
            $hi = '¡Hola'.($s->name ? ' '.explode(' ', trim($s->name))[0] : '').'! 👋';
            $msg = "{$hi} Te recordamos el abono de tu {$prenda} de promoción para tener todo listo en la entrega. 💛\nPuedes pagar o subir tu comprobante con tu documento aquí: {$base}/mi-chaqueta";
            $res = $this->wa->sendText($s->phone, $msg);
            WaMessage::create([
                'phone' => $s->phone, 'lead_id' => null,
                'direction' => 'out', 'body' => $msg.($res['ok'] ? '' : ' (no enviado)'),
            ]);
            $sent++;
        }

        return $sent;
    }

    private function stageMessage(Student $s, string $prenda, string $stage, string $key, string $base): string
    {
        $hi = '¡Hola'.($s->name ? ' '.explode(' ', trim($s->name))[0] : '').'! 👋';

        $extra = match ($key) {
            'toma_medidas'    => "\nSi aún no registras tus medidas, hazlo en {$base}/mi-chaqueta 📏",
            'en_produccion'   => "\n¡Ya estamos fabricando la tuya! 🧵 Escríbenos \"fotos\" y tu documento para ver el avance 📸",
            'control_calidad' => "\nEstamos en la revisión final de calidad ✅",
            'listo'           => "\n🎉 ¡Tu {$prenda} está LISTA! Pronto coordinamos la entrega.",
            'entregado'       => "\n¡Gracias por confiar en nosotros! 💛 Recuerda que tienes garantía de 1 año.",
            default           => '',
        };

        return "{$hi} Novedad de tu {$prenda} de promoción:\nAhora está en la etapa *{$stage}*.{$extra}";
    }
}

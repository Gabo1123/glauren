<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Mail\SimpleMail;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Automatizaciones de venta:
 *  - Resumen diario: a quién llamar hoy.
 *  - Seguimiento automático: si el cliente vio la cotización y no respondió en N días, se le escribe una vez.
 */
class SalesAutomation
{
    public const DEFAULT_FOLLOWUP = "¡Hola {nombre}! 👋 Soy de G'Lauren. Vi que revisaste la cotización {numero}{colegio}. "
        ."¿Te quedó alguna duda o quieres que ajustemos algo (prendas, cantidades o precio)? Aquí está de nuevo: {enlace}";

    public function __construct(private WhatsAppService $wa) {}

    public static function digestEnabled(): bool
    {
        return Setting::get('sales_digest', '1') === '1';
    }

    public static function followupEnabled(): bool
    {
        return Setting::get('auto_followup', '0') === '1';
    }

    public static function followupDays(): int
    {
        return max(1, (int) Setting::get('auto_followup_days', '2'));
    }

    /** Seguimientos agendados para hoy o atrasados. */
    public function dueToday(): Collection
    {
        return Quote::whereIn('status', Quote::OPEN_STATUSES)
            ->whereNotNull('follow_up_at')->whereDate('follow_up_at', '<=', today())
            ->orderBy('follow_up_at')->get();
    }

    /** La vieron y no han respondido (siguen en "Vista" hace más de 1 día). */
    public function viewedNoReply(): Collection
    {
        return Quote::where('status', 'vista')
            ->whereNotNull('viewed_at')->where('viewed_at', '<=', now()->subDay())
            ->orderBy('viewed_at')->get();
    }

    /** Nuevas sin atender (llegaron por la web y nadie las ha enviado). */
    public function newUnattended(): Collection
    {
        return Quote::where('status', 'nuevo')->where('created_at', '<=', now()->subHours(2))->latest()->get();
    }

    /** Envía el resumen al correo (y WhatsApp) del administrador. Devuelve cuántas cotizaciones incluye. */
    public function sendDigest(): int
    {
        $due = $this->dueToday();
        $viewed = $this->viewedNoReply()->whereNotIn('id', $due->pluck('id'));
        $fresh = $this->newUnattended()->whereNotIn('id', $due->pluck('id'));
        $total = $due->count() + $viewed->count() + $fresh->count();
        if ($total === 0) {
            return 0;
        }

        $row = fn (Quote $q, string $tag) => [
            'title' => $q->customer_name.($q->school ? ' · '.$q->school : ''),
            'sub'   => $q->code().' · '.$q->statusLabel().($q->customer_phone ? ' · '.$q->customer_phone : '')
                .($q->viewed_at ? ' · vista '.$q->viewed_at->diffForHumans() : ''),
            'url'   => route('admin.quotes.show', $q),
            'link'  => 'Abrir cotización →',
            'tag'   => $tag,
        ];
        $rows = array_merge(
            $due->map(fn ($q) => $row($q, 'Llamar hoy'))->all(),
            $viewed->map(fn ($q) => $row($q, 'Vio y no respondió'))->all(),
            $fresh->map(fn ($q) => $row($q, 'Nueva sin enviar'))->all(),
        );

        $to = Setting::get('contact_notify_email') ?: config('mail.from.address');
        if ($to) {
            try {
                Mail::to($to)->send(new SimpleMail(
                    '☀️ Hoy toca contactar a '.$total.' cliente(s)',
                    'Tu lista de hoy: '.$total.' cliente(s)',
                    $due->count().' seguimiento(s) para hoy, '.$viewed->count().' vieron la cotización y no han respondido, '.$fresh->count().' nueva(s) sin enviar.',
                    array_slice($rows, 0, 40),
                    'Abrir cotizaciones',
                    route('admin.quotes.index'),
                    now()->locale('es')->isoFormat('dddd D [de] MMMM'),
                ));
            } catch (\Throwable $e) {
                Log::warning('Resumen diario: no se pudo enviar el correo', ['msg' => $e->getMessage()]);
            }
        }

        $phone = Setting::get('admin_2fa_phone');
        if ($phone && $this->wa->configured()) {
            $lines = collect($rows)->take(12)->map(fn ($r) => '• '.$r['title'].' ('.$r['tag'].')')->implode("\n");
            $this->wa->sendText($phone, "☀️ Buenos días. Hoy toca contactar a {$total} cliente(s):\n\n{$lines}\n\n".route('admin.quotes.index'));
        }

        return $total;
    }

    /** Cotizaciones a las que hay que escribir solas (vieron/recibieron y no respondieron en N días). */
    public function followupCandidates(): Collection
    {
        $limit = now()->subDays(self::followupDays());

        return Quote::whereIn('status', ['enviada', 'vista'])
            ->where(fn ($q) => $q->where('viewed_at', '<=', $limit)
                ->orWhere(fn ($q2) => $q2->whereNull('viewed_at')->where('sent_at', '<=', $limit)))
            ->get()
            ->filter(fn (Quote $q) => empty($q->config['auto_followup_at']) && ($q->customer_phone || $q->customer_email));
    }

    public function followupMessage(Quote $q): string
    {
        $tpl = Setting::get('auto_followup_message') ?: self::DEFAULT_FOLLOWUP;

        return strtr($tpl, [
            '{nombre}'  => trim(explode(' ', (string) $q->customer_name)[0]) ?: 'hola',
            '{numero}'  => $q->code(),
            '{colegio}' => $q->school ? ' para '.$q->school : '',
            '{empresa}' => $q->school ?: '',
            '{enlace}'  => $q->publicUrl(),
        ]);
    }

    /** Escribe una sola vez al cliente. Devuelve los canales por los que salió. */
    public function followup(Quote $q): array
    {
        $msg = $this->followupMessage($q);
        $sent = [];

        if ($q->customer_phone && $this->wa->configured()) {
            $res = $this->wa->sendText($q->customer_phone, $msg);
            if ($res['ok'] ?? false) {
                $sent[] = 'WhatsApp';
            }
        }
        if ($q->customer_email) {
            try {
                Mail::to($q->customer_email)->send(new SimpleMail(
                    '¿Alguna duda con tu cotización '.$q->code().'?',
                    '¡Hola '.(trim(explode(' ', (string) $q->customer_name)[0]) ?: '').'!',
                    $msg,
                    [],
                    'Ver mi cotización',
                    $q->publicUrl(),
                    'G\'Lauren · Chaquetas de prom',
                ));
                $sent[] = 'correo';
            } catch (\Throwable $e) {
                Log::warning('Seguimiento automático: correo falló', ['quote' => $q->id, 'msg' => $e->getMessage()]);
            }
        }

        $config = (array) $q->config;
        $config['auto_followup_at'] = now()->toDateTimeString();
        $q->forceFill(['config' => $config, 'follow_up_at' => today()->addDays(2)])->saveQuietly();

        $q->log('seguimiento', $sent
            ? 'Seguimiento automático enviado por '.implode(' y ', $sent).'.'
            : 'Seguimiento automático: no se pudo enviar solo (sin WhatsApp API o fuera de la ventana de 24 h). Escríbele a mano.');

        return $sent;
    }
}

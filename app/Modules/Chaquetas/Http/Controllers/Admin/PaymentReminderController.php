<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;

class PaymentReminderController extends Controller
{
    public const DEFAULT_TEMPLATE = 'Hola {contacto} 👋, te saludamos de {marca}. Sobre las chaquetas de prom de {colegio} ({codigo}): aún hay {pendientes} de {total} estudiantes con el pago pendiente. ¿Coordinamos para completarlo? Quedamos atentos 💛';

    public function __construct(private WhatsAppService $wa) {}

    /** Reemplaza las variables de la plantilla para un contrato. */
    private function buildMessage(string $template, Contract $c, string $brand): string
    {
        return strtr($template, [
            '{contacto}'   => $c->school->contact_name ?: 'equipo',
            '{colegio}'    => $c->school->name ?? 'tu colegio',
            '{codigo}'     => $c->code,
            '{pendientes}' => $c->studentsPendingCount(),
            '{total}'      => $c->studentsTotal(),
            '{marca}'      => $brand,
        ]);
    }

    public function index()
    {
        $contracts = Contract::with('school')
            ->whereHas('students', function ($q) {
                $q->where(function ($w) {
                    $w->where('payment_status', '!=', 'pagado')->orWhereNull('payment_status');
                });
            })
            ->get()
            ->sortByDesc(fn ($c) => $c->studentsPendingCount())
            ->values();

        $template = Setting::get('payment_reminder_template', self::DEFAULT_TEMPLATE);
        $brand    = Setting::get('brand_name', 'Glauren');
        $apiReady = $this->wa->configured();

        return view('admin.finance.reminders', compact('contracts', 'template', 'brand', 'apiReady'));
    }

    /** Envía el recordatorio automáticamente por la API de WhatsApp Business. */
    public function sendAuto(Contract $contract)
    {
        if (! $this->wa->configured()) {
            return back()->withErrors(['wa' => 'La API de WhatsApp no está configurada. Agrega las llaves en tu .env.']);
        }

        $contract->load('school');
        $phone = $contract->school->contact_phone ?? null;
        if (! $phone) {
            return back()->withErrors(['wa' => 'Ese colegio no tiene teléfono de contacto.']);
        }

        $template = Setting::get('payment_reminder_template', self::DEFAULT_TEMPLATE);
        $brand    = Setting::get('brand_name', 'Glauren');
        $message  = $this->buildMessage($template, $contract, $brand);

        $res = $this->wa->sendText($this->wa->normalize($phone), $message);
        if ($res['ok']) {
            $contract->update(['payment_reminder_at' => now()]);

            return back()->with('ok', 'Recordatorio enviado a '.$contract->school->name.'.');
        }

        return back()->withErrors(['wa' => 'No se pudo enviar: '.($res['error'] ?? 'error desconocido')]);
    }

    /** Envía a todos los colegios con pendientes de una vez. */
    public function sendAll()
    {
        if (! $this->wa->configured()) {
            return back()->withErrors(['wa' => 'La API de WhatsApp no está configurada. Agrega las llaves en tu .env.']);
        }

        $template = Setting::get('payment_reminder_template', self::DEFAULT_TEMPLATE);
        $brand    = Setting::get('brand_name', 'Glauren');
        $sent = 0; $failed = 0;

        $contracts = Contract::with('school')->whereHas('students', function ($q) {
            $q->where(function ($w) {
                $w->where('payment_status', '!=', 'pagado')->orWhereNull('payment_status');
            });
        })->get();

        foreach ($contracts as $c) {
            if (! ($c->school->contact_phone ?? null)) {
                $failed++;
                continue;
            }
            $res = $this->wa->sendText($this->wa->normalize($c->school->contact_phone), $this->buildMessage($template, $c, $brand));
            if ($res['ok']) {
                $c->update(['payment_reminder_at' => now()]);
                $sent++;
            } else {
                $failed++;
            }
        }

        return back()->with('ok', "Recordatorios enviados: $sent. Sin enviar: $failed.");
    }

    /** Recordatorio de abono directo a los ESTUDIANTES (los que tienen WhatsApp vinculado). */
    public function remindStudents(Contract $contract, \App\Modules\Chaquetas\Services\OrderNotifier $notifier)
    {
        if (! $this->wa->configured()) {
            return back()->withErrors(['wa' => 'La API de WhatsApp no está configurada. Agrega las llaves en tu .env.']);
        }

        $n = $notifier->remindPayment($contract);

        return back()->with('ok', $n > 0
            ? "Recordatorio de abono enviado a {$n} estudiante(s)."
            : 'No hay estudiantes con WhatsApp vinculado y pago pendiente en este contrato.');
    }

    public function saveTemplate(Request $request)
    {
        $data = $request->validate(['template' => 'required|string|max:1000']);
        Setting::put('payment_reminder_template', $data['template']);

        return back()->with('ok', 'Plantilla del recordatorio guardada.');
    }

    /** Marca que se envió el recordatorio (AJAX, al abrir WhatsApp). */
    public function markSent(Contract $contract)
    {
        $contract->update(['payment_reminder_at' => now()]);

        return response()->json(['ok' => true, 'at' => $contract->payment_reminder_at->format('d M H:i')]);
    }
}

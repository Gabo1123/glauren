<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Models\WaMessage;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    private function audiences(): array
    {
        return [
            'leads_wa'  => 'Contactos de WhatsApp',
            'leads_all' => 'Todos los contactos con teléfono',
            'students'  => 'Estudiantes con WhatsApp (con avisos activos)',
        ];
    }

    /** Devuelve la colección de teléfonos [phone => name] según la audiencia. */
    private function recipients(string $audience): array
    {
        return match ($audience) {
            'students' => Student::whereNotNull('phone')->where('notify_optin', true)
                ->get()->mapWithKeys(fn ($s) => [$this->wa->normalize($s->phone) => $s->name])->filter(fn ($v, $k) => $k)->all(),
            'leads_all' => Lead::whereNotNull('phone')
                ->get()->mapWithKeys(fn ($l) => [$this->wa->normalize($l->phone) => $l->name])->filter(fn ($v, $k) => $k)->all(),
            default => Lead::where('source', 'whatsapp')->whereNotNull('phone')
                ->get()->mapWithKeys(fn ($l) => [$this->wa->normalize($l->phone) => $l->name])->filter(fn ($v, $k) => $k)->all(),
        };
    }

    public function index()
    {
        $audiences = $this->audiences();
        $counts = [];
        foreach (array_keys($audiences) as $a) {
            $counts[$a] = count($this->recipients($a));
        }
        $apiReady = $this->wa->configured();

        return view('admin.cms.broadcast', compact('audiences', 'counts', 'apiReady'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience' => 'required|in:'.implode(',', array_keys($this->audiences())),
            'message'  => 'required|string|max:1000',
        ]);

        if (! $this->wa->configured()) {
            return back()->withErrors(['wa' => 'La API de WhatsApp no está configurada. Agrega las llaves en tu .env.'])->withInput();
        }

        $recipients = $this->recipients($data['audience']);
        $sent = 0; $failed = 0;

        foreach ($recipients as $phone => $name) {
            $res = $this->wa->sendText($phone, $data['message']);
            WaMessage::create([
                'phone' => $phone, 'lead_id' => null,
                'direction' => 'out', 'body' => $data['message'].($res['ok'] ? '' : ' (no enviado)'),
            ]);
            $res['ok'] ? $sent++ : $failed++;
        }

        return back()->with('ok', "Difusión enviada a {$sent} contacto(s)."
            .($failed ? " No se pudo a {$failed} (revisa que estén dentro de la ventana de 24h de Meta)." : ''));
    }
}

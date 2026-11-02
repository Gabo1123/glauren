<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\WaMessage;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('quote')->orderByDesc('ai_paused')->latest()->paginate(30);
        $counts = WaMessage::selectRaw('phone, COUNT(*) as c')->groupBy('phone')->pluck('c', 'phone');
        $needHuman = Lead::needsHuman()->count();

        return view('admin.leads.index', compact('leads', 'counts', 'needHuman'));
    }

    /** Formulario para registrar a mano un contacto que llegó directo (llamada, WhatsApp, referido…). */
    public function create()
    {
        return view('admin.leads.create', [
            'combos'   => QuoteCatalog::combos(),
            'products' => QuoteCatalog::products(),
            'extras'   => QuoteCatalog::extras(),
        ]);
    }

    /**
     * Crea el contacto y su cotización de una vez y lleva directo a enviarla.
     * Si el celular ya existe, no lo duplica: abre la cotización que ya tiene.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'phone'        => 'required|string|max:40',
            'email'        => 'nullable|email|max:160',
            'school'       => 'nullable|string|max:160',
            'quantity'     => 'required|integer|min:1|max:100000',
            'combo'        => ['nullable', 'integer', Rule::in(array_keys(QuoteCatalog::combos()))],
            'products'     => 'nullable|array',
            'products.*'   => ['string', Rule::in(array_keys(QuoteCatalog::products()))],
            'extras'       => 'nullable|array',
            'extras.*'     => ['string', Rule::in(array_keys(QuoteCatalog::extras()))],
            'follow_up_at' => 'nullable|date',
            'notes'        => 'nullable|string|max:5000',
            'send_email'   => 'nullable|boolean',
        ]);

        $digits = substr(preg_replace('/\D/', '', $data['phone']), -10);
        $existing = $digits !== ''
            ? Lead::whereNotNull('phone')->get()->first(fn (Lead $l) => substr(preg_replace('/\D/', '', (string) $l->phone), -10) === $digits)
            : null;
        if ($existing) {
            $quote = Quote::forLead($existing);

            return redirect()->route('admin.quotes.show', $quote)
                ->with('ok', 'Ese celular ya estaba registrado como '.$existing->name.'. Te abrimos su cotización '.$quote->code().'.');
        }

        $lead = Lead::create([
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'email'   => $data['email'] ?? null,
            'school'  => $data['school'] ?? null,
            'message' => $data['notes'] ?? null,
            'source'  => 'manual',
            'ip'      => $request->ip(),
        ]);

        $items = [
            'combo'    => $data['combo'] ?? null,
            'products' => array_values($data['products'] ?? []),
            'extras'   => array_values($data['extras'] ?? []),
        ];
        $unit = QuoteCatalog::summarize($items)['unit_total'];

        $quote = Quote::create([
            'lead_id'         => $lead->id,
            'customer_name'   => $lead->name,
            'customer_phone'  => $lead->phone,
            'customer_email'  => $lead->email,
            'school'          => $lead->school,
            'quantity'        => $data['quantity'],
            'items'           => $items,
            'estimated_price' => $unit * $data['quantity'],
            'status'          => 'nuevo',
            'source'          => 'manual',
            'follow_up_at'    => $data['follow_up_at'] ?? today(),
            'notes'           => $data['notes'] ?? null,
        ]);

        $msg = 'Contacto '.$lead->name.' creado con la cotización '.$quote->code().'.';
        if ($quote->customer_email && $request->boolean('send_email', true)) {
            $msg .= $quote->emailToCustomer() ? ' Se envió a '.$quote->customer_email.'.' : ' No se pudo enviar el correo.';
        }

        return redirect()->route('admin.quotes.show', $quote)->with('ok', $msg.' Envíasela por WhatsApp con el botón de abajo.');
    }

    public function show(Lead $lead)
    {
        $messages = WaMessage::where('phone', $lead->phone)->orderBy('id')->get();
        $lead->load('quote');

        return view('admin.leads.show', compact('lead', 'messages'));
    }

    /** Pausa/reactiva la IA para este contacto (handoff a humano). */
    public function toggleAi(Lead $lead)
    {
        $lead->update([
            'ai_paused'          => ! $lead->ai_paused,
            'human_requested_at' => $lead->ai_paused ? null : now(),
        ]);

        return back()->with('ok', $lead->ai_paused
            ? 'IA pausada: ahora respondes tú por WhatsApp.'
            : 'IA reactivada: vuelve a responder automáticamente.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return back()->with('ok', 'Contacto eliminado.');
    }
}

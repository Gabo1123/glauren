<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::with('jacketType')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->boolean('seguimiento'), fn ($q) => $q
                ->whereIn('status', Quote::OPEN_STATUSES)
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<=', today()))
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = trim($request->input('q'));
                $query->where(fn ($w) => $w
                    ->where('customer_name', 'like', "%$t%")
                    ->orWhere('customer_phone', 'like', "%$t%")
                    ->orWhere('customer_email', 'like', "%$t%")
                    ->orWhere('school', 'like', "%$t%")
                    ->orWhere('number', ltrim(str_ireplace('COT-', '', $t), '#')));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $followUps = Quote::whereIn('status', Quote::OPEN_STATUSES)
            ->whereNotNull('follow_up_at')->whereDate('follow_up_at', '<=', today())->count();

        return view('admin.quotes.index', compact('quotes', 'followUps'));
    }

    public function create()
    {
        return view('admin.quotes.create', [
            'combos'   => QuoteCatalog::combos(),
            'products' => QuoteCatalog::products(),
            'extras'   => QuoteCatalog::extras(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:120',
            'customer_phone' => 'required|string|max:40',
            'customer_email' => 'nullable|email|max:160',
            'school'         => 'nullable|string|max:160',
            'quantity'       => 'required|integer|min:1|max:100000',
            'combo'          => ['nullable', 'integer', Rule::in(array_keys(QuoteCatalog::combos()))],
            'products'       => 'nullable|array',
            'products.*'     => ['string', Rule::in(array_keys(QuoteCatalog::products()))],
            'extras'         => 'nullable|array',
            'extras.*'       => ['string', Rule::in(array_keys(QuoteCatalog::extras()))],
            'follow_up_at'   => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $items = [
            'combo'    => $data['combo'] ?? null,
            'products' => array_values($data['products'] ?? []),
            'extras'   => array_values($data['extras'] ?? []),
        ];
        $unit = QuoteCatalog::summarize($items)['unit_total'];

        $quote = Quote::create([
            'customer_name'   => $data['customer_name'],
            'customer_phone'  => $data['customer_phone'],
            'customer_email'  => $data['customer_email'] ?? null,
            'school'          => $data['school'] ?? null,
            'quantity'        => $data['quantity'],
            'items'           => $items,
            'estimated_price' => $unit * $data['quantity'],
            'status'          => 'nuevo',
            'source'          => 'manual',
            'follow_up_at'    => $data['follow_up_at'] ?? today()->addDays(2),
            'notes'           => $data['notes'] ?? null,
        ]);

        $msg = 'Cotización '.$quote->code().' creada.';
        if ($quote->customer_email) {
            $msg .= $quote->emailToCustomer() ? ' Se envió a '.$quote->customer_email.'.' : ' No se pudo enviar el correo.';
        }

        return redirect()->route('admin.quotes.show', $quote)->with('ok', $msg.' También puedes enviarla por WhatsApp.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['jacketType', 'activities.user']);

        return view('admin.quotes.show', [
            'waMessage' => $quote->whatsappMessage(),
            'others'    => $quote->sameCustomer(),
            'quote'   => $quote,
            'summary' => $quote->summary(),
            'combos'  => QuoteCatalog::combos(),
            'products'=> QuoteCatalog::products(),
            'extras'  => QuoteCatalog::extras(),
        ]);
    }

    /** Envío rápido (desde la lista): mensaje predeterminado, marca enviada y abre WhatsApp. */
    public function send(Quote $quote)
    {
        $quote->markSent();
        $quote->log('whatsapp', 'Cotización enviada por WhatsApp a '.$quote->customer_phone);

        return redirect()->away($quote->whatsappToCustomerUrl());
    }

    /** Envío con mensaje editado en el panel (opcionalmente se guarda como predeterminado). */
    public function whatsapp(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'message'      => 'required|string|max:2000',
            'save_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('save_default')) {
            // Se guarda como plantilla: los datos del cliente vuelven a ser variables.
            Setting::put('quote_whatsapp_message', strtr($data['message'], [
                $quote->publicUrl() => '{enlace}',
                $quote->code()      => '{numero}',
            ]));
        }

        $quote->markSent();
        $quote->log('whatsapp', 'Cotización enviada por WhatsApp a '.$quote->customer_phone);

        return redirect()->away($quote->whatsappToCustomerUrl($data['message']));
    }

    /** Abre la versión para imprimir / guardar en PDF. */
    public function pdf(Quote $quote)
    {
        $quote->log('pdf', 'Se descargó el PDF de la cotización');

        return redirect()->to($quote->publicUrl().'?imprimir=1');
    }

    public function note(Request $request, Quote $quote)
    {
        $data = $request->validate(['note' => 'required|string|max:255']);
        $quote->log('nota', $data['note']);

        return back()->with('ok', 'Nota agregada al historial.');
    }

    /** Envía (o reenvía) la cotización al correo del cliente. */
    public function email(Quote $quote)
    {
        if (! $quote->customer_email) {
            return back()->with('ok', 'Esta cotización no tiene correo del cliente.');
        }

        return back()->with('ok', $quote->emailToCustomer()
            ? 'Cotización enviada a '.$quote->customer_email.'.'
            : 'No se pudo enviar el correo. Revisa la configuración de correo del servidor.');
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status'       => ['required', Rule::in(array_keys(Quote::STATUSES))],
            'notes'        => 'nullable|string',
            'follow_up_at' => 'nullable|date',
            'quantity'     => 'nullable|integer|min:1|max:100000',
            'combo'        => ['nullable', 'integer', Rule::in(array_keys(QuoteCatalog::combos()))],
            'products'     => 'nullable|array',
            'products.*'   => ['string', Rule::in(array_keys(QuoteCatalog::products()))],
            'extras'       => 'nullable|array',
            'extras.*'     => ['string', Rule::in(array_keys(QuoteCatalog::extras()))],
            'edit_items'   => 'nullable|boolean',
        ]);

        $update = [
            'status'       => $data['status'],
            'notes'        => $data['notes'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
        ];

        if ($request->boolean('edit_items')) {
            $items = [
                'combo'    => $data['combo'] ?? null,
                'products' => array_values($data['products'] ?? []),
                'extras'   => array_values($data['extras'] ?? []),
            ];
            $qty = $data['quantity'] ?? $quote->quantity;
            $update['items'] = $items;
            $update['quantity'] = $qty;
            $update['estimated_price'] = QuoteCatalog::summarize($items)['unit_total'] * $qty;
        }

        $quote->update($update);

        return back()->with('ok', 'Cotización actualizada.');
    }
}

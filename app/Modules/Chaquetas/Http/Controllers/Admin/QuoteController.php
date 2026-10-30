<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Quote;
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

        return redirect()->route('admin.quotes.show', $quote)->with('ok', 'Cotización '.$quote->code().' creada. Ya puedes enviarla por WhatsApp.');
    }

    public function show(Quote $quote)
    {
        $quote->load('jacketType');

        return view('admin.quotes.show', [
            'quote'   => $quote,
            'summary' => $quote->summary(),
            'combos'  => QuoteCatalog::combos(),
            'products'=> QuoteCatalog::products(),
            'extras'  => QuoteCatalog::extras(),
        ]);
    }

    /** Marca la cotización como enviada y abre WhatsApp con el enlace para el cliente. */
    public function send(Quote $quote)
    {
        $quote->sent_at = $quote->sent_at ?? now();
        if (in_array($quote->status, ['nuevo', 'contactado'], true)) {
            $quote->status = 'enviada';
        }
        if (! $quote->follow_up_at || $quote->follow_up_at->lt(today()->addDays(2))) {
            $quote->follow_up_at = today()->addDays(2);
        }
        $quote->save();

        return redirect()->away($quote->whatsappToCustomerUrl());
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

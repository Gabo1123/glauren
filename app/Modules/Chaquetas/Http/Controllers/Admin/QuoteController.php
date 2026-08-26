<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::with('jacketType')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = trim($request->input('q'));
                $query->where(fn ($w) => $w
                    ->where('customer_name', 'like', "%$t%")
                    ->orWhere('customer_phone', 'like', "%$t%")
                    ->orWhere('customer_email', 'like', "%$t%")
                    ->orWhere('school', 'like', "%$t%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote)
    {
        $quote->load('jacketType');

        return view('admin.quotes.show', compact('quote'));
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status' => 'required|in:nuevo,contactado,cerrado,cancelado',
            'notes'  => 'nullable|string',
        ]);

        $quote->update($data);

        return back()->with('ok', 'Cotización actualizada.');
    }
}

<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use Illuminate\Support\Facades\Auth;

/**
 * Cotización que ve el cliente: /cotizacion/{token}
 * Registra cuándo la abrió para que el asesor sepa a quién llamar.
 */
class QuotePublicController extends Controller
{
    public function show(string $token)
    {
        $quote = Quote::where('public_token', $token)->firstOrFail();

        // Las visitas del propio administrador no cuentan como "vista".
        $isAdmin = Auth::check() && Auth::user()->isAdmin();
        if (! $isAdmin) {
            $quote->view_count = $quote->view_count + 1;
            if (! $quote->viewed_at) {
                $quote->viewed_at = now();
            }
            if (in_array($quote->status, ['nuevo', 'contactado', 'enviada'], true)) {
                $quote->status = 'vista';
            }
            $quote->save();
        }

        $whatsapp = preg_replace('/\D/', '', (string) (config('chaquetas.whatsapp') ?: Setting::get('contact_whatsapp', '')));
        $replyUrl = $whatsapp
            ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode("¡Hola! Vi la cotización {$quote->code()} y quiero separar la fecha para mi promoción.")
            : null;

        return view('chaquetas.cotizacion', [
            'quote'    => $quote,
            'summary'  => $quote->summary(),
            'products' => QuoteCatalog::products(),
            'combos'   => QuoteCatalog::combos(),
            'extras'   => QuoteCatalog::extras(),
            'cfg'      => config('cotizacion'),
            'replyUrl' => $replyUrl,
            'phone'    => Setting::get('contact_phone', '302 110 6592'),
        ]);
    }
}

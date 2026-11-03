@php
    use App\Modules\Chaquetas\Support\QuoteCatalog as QC;
    use App\Modules\Chaquetas\Support\QuotePages;
    $m = fn ($v) => QC::money($v);
    $first = \Illuminate\Support\Str::of($quote->customer_name)->explode(' ')->first();
    $business = $quote->isBusiness();
    if ($business) {
        $pages = \App\Modules\Chaquetas\Support\QuoteBusinessPages::pages($quote);
    } else {
        $pages = [];
        foreach (QuotePages::pages($quote) as $i => $h) {
            $pages[] = ['class' => 'pg-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT), 'html' => $h];
        }
    }
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cotización {{ $quote->code() }} · G'Lauren</title>
<link rel="icon" href="/favicon.ico">
<link rel="preload" href="/fonts/cotizacion/anton-400.woff2" as="font" type="font/woff2" crossorigin>
<style>
@font-face{font-family:'Anton';font-style:normal;font-weight:400;font-display:swap;src:url(/fonts/cotizacion/anton-400.woff2) format('woff2')}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:400;font-display:swap;src:url(/fonts/cotizacion/montserrat-400.woff2) format('woff2')}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:500;font-display:swap;src:url(/fonts/cotizacion/montserrat-500.woff2) format('woff2')}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:600;font-display:swap;src:url(/fonts/cotizacion/montserrat-600.woff2) format('woff2')}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:700;font-display:swap;src:url(/fonts/cotizacion/montserrat-700.woff2) format('woff2')}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:800;font-display:swap;src:url(/fonts/cotizacion/montserrat-800.woff2) format('woff2')}
*{box-sizing:border-box}
html,body{margin:0;overflow-x:hidden}
body{background:#2B2B2B;color:#0E0E0E;font-family:'Montserrat',sans-serif;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.y{color:#FFC72C}
.lbl{font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:#FFC72C}

/* Barra superior: solo en la web */
.top{background:#0E0E0E;color:#fff;border-bottom:6px solid #FF6B1A}
.top .in{max-width:794px;margin:0 auto;padding:18px 14px;display:flex;flex-direction:column;gap:14px}
.top h1{margin:0;font-family:'Anton',sans-serif;font-weight:400;font-size:clamp(24px,5vw,34px);line-height:1.05;text-transform:uppercase}
.top p{margin:0;font-size:14px;color:#d6d6d6}
.track{display:grid;grid-template-columns:repeat(6,1fr);gap:6px;counter-reset:st}
.track div{background:#1A1A1A;border:1.5px solid #333;border-radius:12px;padding:9px 6px;font-size:11px;font-weight:700;text-align:center;color:#999;line-height:1.25}
.track div:before{counter-increment:st;content:counter(st);display:block;margin:0 auto 5px;width:24px;height:24px;line-height:24px;border-radius:50%;background:#333;color:#ccc;font-family:'Anton'}
.track div.done{border-color:#FFC72C;color:#fff}
.track div.done:before{content:"✓";background:#FFC72C;color:#0E0E0E}
.track div.cur{border-color:#FF6B1A;color:#fff;box-shadow:0 0 0 3px rgba(255,107,26,.3)}
.track div.cur:before{background:#FF6B1A;color:#0E0E0E}
.mine{background:#fff;color:#0E0E0E;border-radius:16px;overflow:hidden;border:3px solid #FFC72C}
.mine table{width:100%;border-collapse:collapse}
.mine td{padding:10px 14px;border-top:1px solid #e6e6e6;font-size:14px;font-weight:600}
.mine td:last-child{text-align:right;font-family:'Anton';font-size:20px;white-space:nowrap}
.mine .tot td{background:#FFC72C;font-weight:800}
.mine caption{caption-side:top;text-align:left;padding:10px 14px;background:#0E0E0E;color:#FFC72C;font-size:11px;font-weight:800;letter-spacing:3px}

/* Hojas: idénticas al PDF (794 × 1123 px = A4), escaladas al ancho de la pantalla */
.sheets{padding:16px 0 110px;display:flex;flex-direction:column;align-items:center;gap:16px}
.sheet{width:calc(794px * var(--s,1));height:calc(1123px * var(--s,1));position:relative;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.45);background:#fff}
.sheet>.pg{width:794px;height:1123px;transform:scale(var(--s,1));transform-origin:0 0;position:absolute;left:0;top:0}
.pg img{max-width:none}

/* Botones flotantes */
.bar{position:fixed;left:0;right:0;bottom:0;z-index:20;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;padding:12px;background:rgba(14,14,14,.92);backdrop-filter:blur(6px);border-top:3px solid #FFC72C}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 22px;border-radius:999px;background:#FFC72C;color:#0E0E0E;font-weight:800;font-size:15px;text-decoration:none;border:0;cursor:pointer;font-family:inherit}
.btn.w{background:#25d366;color:#04240f}

@media (max-width:600px){ .track{grid-template-columns:repeat(3,1fr)} .btn{flex:1 1 100%} }

@page{size:A4;margin:0}
@media print{
  html,body{background:#fff}
  .noprint{display:none!important}
  .sheets{padding:0;gap:0;display:block}
  .sheet{--s:1!important;width:210mm;height:297mm;box-shadow:none;break-after:page;page-break-after:always}
  .sheet:last-child{break-after:auto;page-break-after:auto}
  .sheet>.pg{transform:none}
}
{!! QuotePages::css() !!}
@if($business)
@include('chaquetas.empresa.styles')
@endif
</style>
</head>
<body>

<header class="top noprint">
    <div class="in">
        <div>
            <h1>Hola {{ $first }}, esta es tu <span class="y">cotización {{ $quote->code() }}</span></h1>
            <p>{{ $business ? 'Revisa cada prenda con su valor al por mayor. Puedes descargarla en PDF o escribirnos por WhatsApp para aprobarla o ajustarla.' : 'Revísala con calma. Puedes descargarla en PDF o escribirnos por WhatsApp para separar tu fecha.' }}</p>
        </div>
        <div class="lbl">Estado de tu proceso</div>
        <div class="track" style="margin-top:-6px">
            @foreach($quote->customerSteps() as $st)
                <div class="{{ $st['done'] ? 'done' : '' }} {{ $st['current'] ? 'cur' : '' }}">{{ $st['label'] }}</div>
            @endforeach
        </div>
        @if(count($summary['lines']))
            <div class="mine">
                <table>
                    <caption>TU SELECCIÓN</caption>
                    @foreach($summary['lines'] as $l)
                        <tr><td>{{ $l['label'] }}</td><td>{{ $m($l['price']) }}</td></tr>
                    @endforeach
                    @if($business)
                        <tr class="tot"><td>Total · {{ $quote->businessTotals()['units'] }} unidades</td><td>{{ $m($summary['unit_total']) }}</td></tr>
                    @else
                    <tr class="tot"><td>Valor por estudiante</td><td>{{ $m($summary['unit_total']) }}</td></tr>
                    @endif
                    @if(! $business && $quote->quantity > 1)
                        <tr class="tot"><td>Total aproximado · {{ $quote->quantity }} estudiantes</td><td>{{ $m($summary['unit_total'] * $quote->quantity) }}</td></tr>
                    @endif
                </table>
            </div>
        @endif
    </div>
</header>

<main class="sheets" id="sheets">
    @foreach($pages as $page)
        <div class="sheet"><div class="pg {{ $page['class'] }}">{!! $page['html'] !!}</div></div>
    @endforeach
</main>

<nav class="bar noprint">
    @if($replyUrl)<a class="btn w" href="{{ $replyUrl }}" target="_blank" rel="noopener">{{ $business ? 'Aprobar o ajustar por WhatsApp' : 'Quiero separar mi fecha por WhatsApp' }}</a>@endif
    <button class="btn" type="button" onclick="window.print()">Descargar en PDF</button>
</nav>

<script>
(function () {
    var root = document.getElementById('sheets');
    function fit() {
        var w = Math.min(794, document.documentElement.clientWidth - 16);
        root.style.setProperty('--s', (w / 794).toFixed(4));
    }
    fit();
    window.addEventListener('resize', fit);
    window.addEventListener('beforeprint', function () { root.style.setProperty('--s', 1); });
    window.addEventListener('afterprint', fit);
})();
</script>
@if(request()->boolean('imprimir'))
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 900));</script>
@endif
</body>
</html>

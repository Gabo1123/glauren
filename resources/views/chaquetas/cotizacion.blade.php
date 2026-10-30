@php
    use App\Modules\Chaquetas\Support\QuoteCatalog as QC;
    $m = fn ($v) => QC::money($v);
    $first = \Illuminate\Support\Str::of($quote->customer_name)->explode(' ')->first();
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cotización {{ $quote->code() }} · G'Lauren</title>
<link rel="icon" href="/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--y:#FFC72C;--o:#FF6B1A;--k:#0E0E0E;--k2:#1A1A1A;--p:#F7F6F2;--line:#E6E6E6;--muted:#444}
*{box-sizing:border-box}
html,body{overflow-x:hidden}
body{margin:0;background:var(--p);color:var(--k);font-family:'Montserrat',sans-serif;line-height:1.5}
.wrap{max-width:980px;margin:0 auto;padding:0 20px}
.d{font-family:'Anton',sans-serif;font-weight:400;letter-spacing:.3px;text-transform:uppercase;line-height:1}
.hl{background:var(--y);padding:0 .2em}
.kick{display:flex;align-items:center;gap:10px;font-size:12px;font-weight:800;letter-spacing:4px;text-transform:uppercase}
.kick:before{content:"";width:34px;height:5px;background:var(--o)}
section{padding:56px 0}
h2{font-size:clamp(34px,6vw,52px);margin:.25em 0 .4em}

/* Portada */
.hero{background:var(--k);color:#fff;position:relative;overflow:hidden;padding:0}
.hero:before{content:"";position:absolute;inset:0;background:var(--y);clip-path:polygon(66% 0,100% 0,100% 100%,40% 100%)}
.hero:after{content:"";position:absolute;inset:0;background:var(--o);clip-path:polygon(59% 0,63% 0,37% 100%,33% 100%)}
.hero .wrap{position:relative;z-index:1;display:grid;grid-template-columns:1.1fr .9fr;gap:20px;align-items:center;min-height:560px;padding-top:36px;padding-bottom:36px}
.hero .logo{width:180px}
.hero h1{font-size:clamp(70px,13vw,150px);margin:18px 0 0}
.hero h1 span{color:var(--y);display:block}
.hero .sub{font-size:18px;font-weight:600;max-width:380px;margin:14px 0 0}
.hero img.jk{width:100%;max-width:440px;justify-self:end;filter:drop-shadow(0 20px 30px rgba(0,0,0,.35))}
.num{position:absolute;right:20px;top:24px;z-index:2;background:var(--k);color:var(--y);padding:10px 16px;text-align:right}
.num small{display:block;color:#fff;font-size:10px;font-weight:800;letter-spacing:3px}
.num b{font-family:'Anton';font-size:30px;font-weight:400}
.client{background:var(--k);color:#fff;border-top:8px solid var(--o)}
.client .wrap{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding-top:24px;padding-bottom:24px}
.client small{display:block;color:var(--y);font-size:11px;font-weight:800;letter-spacing:3px}
.client b{font-size:16px}
.client span{color:#d6d6d6;font-size:13px}

/* Selección */
.mine{background:#fff;border:3px solid var(--k);border-radius:20px;box-shadow:8px 8px 0 var(--y);overflow:hidden}
.mine table{width:100%;border-collapse:collapse}
.mine td{padding:14px 20px;border-top:1px solid var(--line);font-size:15px}
.mine td:last-child{text-align:right;font-family:'Anton';font-size:22px;white-space:nowrap}
.mine .tot td{background:var(--k);color:#fff}
.mine .tot td:last-child{color:var(--y);font-size:30px}

/* Beneficios */
.dark{background:var(--k);color:#fff}
.big3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.big{border-radius:20px;padding:22px;color:var(--k)}
.big .v{font-family:'Anton';font-size:72px;line-height:.95}
.big .t{font-family:'Anton';font-size:20px;text-transform:uppercase}
.big p{margin:6px 0 0;font-size:13px;font-weight:600}
.inc{background:var(--y);border-radius:22px;padding:20px;margin-top:16px;color:var(--k)}
.inc .g{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:12px}
.inc .g div{background:#fff;border-radius:14px;padding:14px;text-align:center;font-family:'Anton';font-size:18px;text-transform:uppercase}
.cond{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px}
.cond div{background:var(--k2);border:1.5px solid #333;border-radius:16px;padding:16px}
.cond b{font-family:'Anton';font-weight:400;font-size:18px;text-transform:uppercase;color:#fff}
.cond p{margin:4px 0 0;font-size:13px;color:#d9d9d9}

/* Precios */
.tb{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:2px solid var(--k);border-radius:16px;overflow:hidden}
.tb th{background:var(--k);color:var(--y);font-size:11px;letter-spacing:2px;text-align:left;padding:12px 16px}
.tb td{padding:12px 16px;border-top:1px solid #e2e0d8;font-size:14px}
.tb td.p{text-align:right;font-family:'Anton';font-size:22px;white-space:nowrap}
.pill{display:inline-block;min-width:30px;text-align:center;padding:4px 9px;border-radius:999px;background:var(--y);font-weight:800;font-size:13px}
.photos{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:22px 0}
.photos img{width:100%;height:260px;object-fit:cover;border-radius:16px;border:3px solid var(--k)}

/* Combos */
.combos{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.combo{display:flex;background:#fff;border:2px solid var(--k);border-radius:18px;overflow:hidden}
.combo .n{flex:0 0 70px;background:var(--k);color:var(--y);display:flex;flex-direction:column;align-items:center;justify-content:center}
.combo .n small{color:#fff;font-size:9px;font-weight:800;letter-spacing:2px}
.combo .n b{font-family:'Anton';font-weight:400;font-size:44px;line-height:1}
.combo .it{flex:1;padding:12px 14px;font-size:13px;font-weight:700;display:flex;flex-direction:column;justify-content:center;gap:4px}
.combo .it em{font-style:normal;font-size:11px;background:var(--o);border-radius:6px;padding:2px 6px;align-self:flex-start}
.combo .pr{flex:0 0 118px;background:var(--y);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:8px}
.combo .pr s{font-size:12px;font-weight:700;opacity:.7}
.combo .pr b{font-family:'Anton';font-weight:400;font-size:26px;line-height:1.05}
.combo .pr small{font-size:10px;font-weight:700}
.combo.sel{box-shadow:0 0 0 4px var(--o)}

/* Adicionales / foto */
.ad{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.ad div{display:flex;justify-content:space-between;align-items:center;gap:12px;background:var(--k2);border:1.5px solid #333;border-radius:14px;padding:12px 16px;font-weight:700;font-size:14px}
.ad div b{font-family:'Anton';font-weight:400;font-size:22px;color:var(--y);white-space:nowrap}
.ad div.hi{grid-column:1/-1;background:var(--y);color:var(--k);border-color:var(--y)}
.ad div.hi b{color:var(--k)}
.pkg{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;align-items:center;margin-top:30px}
.pkg ul{margin:0;padding:0;list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.pkg li{background:var(--k2);border:1.5px solid #333;border-radius:12px;padding:10px 12px;font-size:13px;font-weight:700}
.tag{background:var(--y);color:var(--k);border:3px solid var(--k);border-radius:20px;padding:26px 20px 16px;text-align:center;transform:rotate(2deg);box-shadow:8px 8px 0 var(--o);position:relative}
.tag .r{position:absolute;left:50%;top:-18px;transform:translateX(-50%) rotate(-3deg);background:var(--o);border:3px solid var(--k);border-radius:999px;padding:3px 14px;font-family:'Anton';font-size:18px;white-space:nowrap}
.tag s{font-family:'Anton';font-size:26px;text-decoration-color:#D7261E;text-decoration-thickness:4px;opacity:.75}
.tag b{display:block;font-family:'Anton';font-weight:400;font-size:56px;line-height:1}

/* CTA */
.cta{background:var(--y);text-align:center}
.cta h2{margin-top:0}
.btn{display:inline-flex;align-items:center;gap:10px;min-height:52px;padding:0 28px;border-radius:999px;background:var(--k);color:var(--y);font-weight:800;font-size:16px;text-decoration:none}
.btn.w{background:#25d366;color:#04240f}
.foot{background:var(--k);color:#bdbdbd;font-size:12px;padding:20px 0;text-align:center}

@media (max-width:760px){
  .hero .wrap{grid-template-columns:1fr;min-height:0}
  .hero:before{clip-path:polygon(0 80%,100% 66%,100% 100%,0 100%)}
  .hero:after{clip-path:polygon(0 77%,100% 63%,100% 64.5%,0 78.5%)}
  .hero img.jk{max-width:300px;justify-self:center}
  .client .wrap,.big3,.inc .g,.cond,.combos,.ad,.pkg,.pkg ul,.photos{grid-template-columns:1fr}
  .photos img{height:220px}
  .tb th:nth-child(3),.tb td:nth-child(3){display:none}
  .num{position:static;display:inline-block;margin:16px 20px 0}
}
@page{size:A4;margin:10mm 0}
@media print{
  .noprint{display:none!important}
  .hero .wrap{min-height:430px}
  .combos{grid-template-columns:repeat(2,1fr)}
  .photos img{height:200px}
  section{padding:28px 0;break-inside:avoid}
  body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
</head>
<body>

<header class="hero">
    <div class="num"><small>COTIZACIÓN N°</small><b>{{ $quote->number }}</b></div>
    <div class="wrap">
        <div>
            <img class="logo" src="/img/cotizacion/logo-light.png" alt="G'Lauren">
            <h1 class="d">PROM<span>{{ now()->month >= 8 ? now()->year + 1 : now()->year }}</span></h1>
            <p class="sub">Hola {{ $first }}, esta es tu cotización de chaquetas, busos y prendas personalizadas.</p>
        </div>
        <img class="jk" src="/img/cotizacion/hero.png" alt="Chaqueta de prom estilo varsity">
    </div>
</header>
<div class="client">
    <div class="wrap">
        <div><small>PARA</small><b>{{ $quote->customer_name }}</b><br><span>{{ $quote->customer_phone }}</span></div>
        <div><small>COLEGIO</small><b>{{ $quote->school ?: '—' }}</b><br><span>{{ $quote->quantity }} estudiante{{ $quote->quantity == 1 ? '' : 's' }}</span></div>
        <div><small>FECHA</small><b>{{ $quote->created_at->locale('es')->translatedFormat('d \d\e F Y') }}</b><br><span>Cotización {{ $quote->code() }}</span></div>
        <div><small>DE</small><b>G'LAUREN</b><br><span>{{ $phone }} · chaquetasdeprom.com</span></div>
    </div>
</div>

@if(count($summary['lines']))
<section>
    <div class="wrap">
        <div class="kick">Tu selección</div>
        <h2 class="d">Lo que <span class="hl">cotizaste</span></h2>
        <div class="mine">
            <table>
                @foreach($summary['lines'] as $l)
                    <tr><td>{{ $l['label'] }}</td><td>{{ $m($l['price']) }}</td></tr>
                @endforeach
                <tr class="tot"><td><b>Valor por estudiante</b></td><td>{{ $m($summary['unit_total']) }}</td></tr>
                @if($quote->quantity > 1)
                    <tr class="tot"><td><b>Total aproximado · {{ $quote->quantity }} estudiantes</b></td><td>{{ $m($summary['unit_total'] * $quote->quantity) }}</td></tr>
                @endif
            </table>
        </div>
    </div>
</section>
@endif

<section class="dark">
    <div class="wrap">
        <div class="kick" style="color:var(--y)">Por qué G'Lauren</div>
        <h2 class="d">Tus <span style="color:var(--y)">beneficios</span></h2>
        <div class="big3">
            @foreach($cfg['benefits'] as $i => $b)
                <div class="big" style="background:{{ ['var(--y)','var(--o)','#fff'][$i % 3] }}"><div class="v">{{ $b['big'] }}</div><div class="t">{{ $b['title'] }}</div><p>{{ $b['text'] }}</p></div>
            @endforeach
        </div>
        <div class="inc">
            <div class="d" style="font-size:30px">Incluido en tu prom</div>
            <div class="g">@foreach($cfg['included'] as $x)<div>{{ $x }}</div>@endforeach</div>
        </div>
        <div class="cond">
            @foreach($cfg['conditions'] as $c)<div><b>{{ $c['title'] }}</b><p>{{ $c['text'] }}</p></div>@endforeach
        </div>
    </div>
</section>

<section>
    <div class="wrap">
        <div class="kick">Costo al por mayor</div>
        <h2 class="d">Valores <span class="hl">unitarios</span></h2>
        <div style="overflow-x:auto"><table class="tb">
            <thead><tr><th>PRENDA</th><th>BORDADOS</th><th>TELA</th><th style="text-align:right">VALOR</th></tr></thead>
            <tbody>
            @foreach($products as $p)
                <tr><td><b>{{ $p['name'] }}</b></td><td><span class="pill">{{ $p['embroidery'] }}</span></td><td>{{ $p['fabric'] }}</td><td class="p">{{ $m($p['price']) }}</td></tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="photos">
            <img src="/img/cotizacion/chaqueta.jpg" alt="Chaqueta con cortes Prom">
            <img src="/img/cotizacion/beisbolera.jpg" alt="Beisbolera estilo varsity">
            <img src="/img/cotizacion/buso.jpg" alt="Buso tipo canguro">
        </div>
    </div>
</section>

<section style="padding-top:0">
    <div class="wrap">
        <div class="kick">Ahorra combinando</div>
        <h2 class="d">Combos <span class="hl">−{{ (int) round($cfg['combo_discount'] * 100) }}%</span></h2>
        <div class="combos">
            @foreach($combos as $c)
                <div class="combo {{ ($quote->items['combo'] ?? null) == $c['number'] ? 'sel' : '' }}">
                    <div class="n"><small>COMBO</small><b>{{ $c['number'] }}</b></div>
                    <div class="it">{{ implode(' + ', $c['names']) }}@if($c['gift'])<em>+ {{ $c['gift'] }}</em>@endif</div>
                    <div class="pr"><s>{{ $m($c['full']) }}</s><b>{{ $m($c['price']) }}</b><small>por estudiante</small></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="dark">
    <div class="wrap">
        <div class="kick" style="color:var(--y)">Personaliza aún más</div>
        <h2 class="d">Adicionales</h2>
        <div class="ad">
            @foreach($extras as $e)
                <div class="{{ !empty($e['highlight']) ? 'hi' : '' }}">{{ $e['name'] }}<b>{{ $m($e['price']) }}</b></div>
            @endforeach
        </div>
        <div class="pkg">
            <div>
                <div class="kick" style="color:var(--y)">Ceremonia de grado</div>
                <h2 class="d" style="font-size:40px">Paquete de <span style="color:var(--y)">fotografía</span></h2>
                <ul>@foreach($cfg['photo_package']['items'] as $x)<li>{{ $x }}</li>@endforeach</ul>
                <p style="margin-top:14px;font-weight:700;color:var(--y)">{{ $cfg['photo_package']['benefit'] }}</p>
            </div>
            <div class="tag"><span class="r">¡PROMOCIÓN!</span>ANTES <s>{{ $m($cfg['photo_package']['before']) }}</s><b>{{ $m($cfg['photo_package']['price']) }}</b>por estudiante</div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="wrap">
        <h2 class="d">¿Listos para su prom?</h2>
        <p style="font-weight:600;margin-bottom:22px">Escríbenos y separamos la fecha de toma de medidas para tu colegio.</p>
        <div class="noprint" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            @if($replyUrl)<a class="btn w" href="{{ $replyUrl }}" target="_blank" rel="noopener">Quiero separar mi fecha por WhatsApp</a>@endif
            <a class="btn" href="javascript:window.print()">Descargar en PDF</a>
        </div>
    </div>
</section>
<div class="foot">Cotización {{ $quote->code() }} · No válida como factura de compra · G'Lauren · NIT 1033779133-6 · chaquetasdeprom.com</div>
@if(request()->boolean('imprimir'))
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 700));</script>
@endif
</body>
</html>

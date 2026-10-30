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


/* Seguimiento */
.track{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;counter-reset:st}
.track div{position:relative;background:#fff;border:2px solid #ddd;border-radius:14px;padding:12px 10px;font-size:12px;font-weight:700;text-align:center;color:#777}
.track div:before{counter-increment:st;content:counter(st);display:block;margin:0 auto 6px;width:28px;height:28px;line-height:28px;border-radius:50%;background:#e9e7e0;color:#555;font-family:'Anton'}
.track div.done{border-color:var(--k);color:var(--k)}
.track div.done:before{content:"✓";background:var(--k);color:var(--y)}
.track div.cur{border-color:var(--o);color:var(--k);box-shadow:0 0 0 3px rgba(255,107,26,.25)}
.track div.cur:before{background:var(--o);color:var(--k)}
/* Quiénes somos */
.about{display:grid;grid-template-columns:1.2fr .8fr;gap:30px;align-items:center}
.about .pic{position:relative}
.about .pic img{width:100%;height:340px;object-fit:cover;border-radius:18px;border:3px solid var(--k);box-shadow:12px 12px 0 var(--y)}
.about .seal{position:absolute;left:-18px;bottom:-14px;width:96px;height:96px;border-radius:50%;background:var(--o);border:3px solid var(--k);display:flex;flex-direction:column;align-items:center;justify-content:center;transform:rotate(-8deg);font-family:'Anton';font-size:30px;line-height:1}
.about .seal small{font-family:'Montserrat';font-size:10px;font-weight:800}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:26px}
.stats div{background:#fff;border:2px solid var(--k);border-radius:16px;padding:14px;box-shadow:4px 4px 0 var(--y)}
.stats b{display:block;font-family:'Anton';font-weight:400;font-size:38px;line-height:1}
.stats span{font-size:12px;font-weight:700}
/* Plataforma */
.portals{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.portal{background:var(--k2);border:1.5px solid #333;border-radius:20px;padding:22px}
.portal h3{font-family:'Anton';font-weight:400;font-size:26px;margin:0 0 12px;color:#fff}
.portal ul{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.portal li{font-size:14px;color:#e2e2e2;padding-left:26px;position:relative}
.portal li:before{content:"✓";position:absolute;left:0;top:0;width:18px;height:18px;border-radius:50%;background:var(--y);color:var(--k);font-size:11px;font-weight:800;text-align:center;line-height:18px}
/* Galerías */
.gal{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.gal img{width:100%;height:220px;object-fit:cover;border-radius:14px;border:3px solid var(--k);display:block}
.gal .wide{grid-column:span 2}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
.chips span{border:1.5px solid #444;background:#1a1a1a;color:#fff;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700}
.brands{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.brands div{background:#fff;border:1.5px solid var(--line);border-radius:16px;height:130px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.brands img{max-width:100%;max-height:100%;object-fit:contain}
.tech{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-top:16px}
.tech img{width:100%;height:110px;object-fit:contain;background:#fff;border-radius:14px;padding:8px;box-sizing:border-box}
.cat{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.cat img{width:100%;height:170px;object-fit:contain;background:#fff;border:1.5px solid var(--line);border-radius:14px;padding:6px;box-sizing:border-box}
.cat .ph{grid-row:span 2;object-fit:cover;padding:0;border:3px solid var(--k);height:350px}
.cat-h{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin:34px 0 14px;flex-wrap:wrap}
.cat-h h3{font-family:'Anton';font-weight:400;font-size:32px;text-transform:uppercase;margin:0}
.cat-h span{background:var(--k);color:var(--y);font-family:'Anton';font-size:22px;padding:6px 14px;border-radius:10px}
/* Proceso */
.phase{display:grid;grid-template-columns:120px 1fr;gap:14px;margin-bottom:14px}
.phase .tab{border-radius:16px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px}
.phase .tab b{font-family:'Anton';font-weight:400;font-size:46px;line-height:1}
.phase .tab small{font-size:10px;font-weight:800;letter-spacing:2px}
.phase .tab span{font-size:11px;font-weight:800;text-transform:uppercase}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.step{background:#fff;border:2px solid var(--k);border-radius:16px;padding:14px;box-shadow:4px 4px 0 var(--y)}
.step i{font-style:normal;display:inline-block;width:32px;height:32px;line-height:32px;text-align:center;border-radius:50%;background:var(--k);color:var(--y);font-family:'Anton'}
.step b{display:block;font-family:'Anton';font-weight:400;font-size:18px;text-transform:uppercase;margin:8px 0 4px}
.step p{margin:0;font-size:13px;color:#2a2a2a}

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
  .track{grid-template-columns:repeat(2,1fr)}
  .about,.portals,.stats,.brands,.phase,.steps{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr 1fr}
  .gal,.cat{grid-template-columns:1fr 1fr}
  .gal .wide{grid-column:span 2}
  .tech{grid-template-columns:repeat(3,1fr)}
  .cat .ph{grid-column:span 2;grid-row:auto;height:260px}

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

<section style="padding:34px 0 0">
    <div class="wrap">
        <div class="kick">Estado de tu proceso</div>
        <div class="track" style="margin-top:14px">
            @foreach($quote->customerSteps() as $st)
                <div class="{{ $st['done'] ? 'done' : '' }} {{ $st['current'] ? 'cur' : '' }}">{{ $st['label'] }}</div>
            @endforeach
        </div>
    </div>
</section>

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

<section>
    <div class="wrap">
        <div class="about">
            <div>
                <div class="kick">Quiénes somos</div>
                <h2 class="d">Hacemos que cada prom sea <span class="hl">única y memorable</span></h2>
                <p style="font-size:16px;line-height:1.7;margin:0">Estimados padres de familia: nos enorgullece presentarles <b>G'Lauren</b>. Llevamos más de 10 años creando chaquetas y prendas personalizadas para promociones escolares y empresas, con dedicación, compromiso y alta calidad.</p>
            </div>
            <div class="pic"><img src="/img/cotizacion/trabajos/trabajo-2.jpg" alt="Promoción con chaquetas G'Lauren"><div class="seal">+10<small>AÑOS</small></div></div>
        </div>
        <div class="stats">
            <div><b>+10</b><span>años vistiendo promociones</span></div>
            <div><b>+25</b><span>colegios ya visten G'Lauren</span></div>
            <div><b>3</b><span>marcas aliadas: Lafayette, Patprimo y YES</span></div>
            <div><b>2</b><span>portales para seguir tu pedido</span></div>
        </div>
    </div>
</section>

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

<section class="dark">
    <div class="wrap">
        <div class="kick" style="color:var(--y)">Plataforma G'Lauren</div>
        <h2 class="d">No vendemos una prenda, <span style="color:var(--y)">vendemos una experiencia</span></h2>
        <p style="color:#e2e2e2;max-width:700px;margin:0 0 22px">Dos portales, toda una experiencia: el colegio administra su pedido y cada estudiante sigue su chaqueta, arma su perfil, sube sus fotos y comparte con su curso. Más que una chaqueta: un recuerdo.</p>
        <div class="portals">
            <div class="portal"><h3>Para el colegio</h3><ul><li>Contrato y firma en línea</li><li>Avance de fabricación con fotos reales</li><li>Control de pagos por estudiante</li><li>Lista del curso y medidas</li></ul></div>
            <div class="portal"><h3>Para el estudiante</h3><ul><li>Seguimiento de su pedido con fotos</li><li>Su perfil con foto y datos</li><li>Subir sus propias fotos</li><li>Muro del curso: escribirse entre compañeros</li></ul></div>
        </div>
    </div>
</section>

<section>
    <div class="wrap">
        <div class="kick">Portafolio</div>
        <h2 class="d">Nuestros <span class="hl">trabajos</span></h2>
        <div class="gal">
            <img class="wide" src="/img/cotizacion/trabajos/trabajo-3.jpg" alt="Promoción con busos Prom" loading="lazy">
            <img src="/img/cotizacion/trabajos/trabajo-6.jpg" alt="Estudiantes con chaquetas personalizadas" loading="lazy">
            <img src="/img/cotizacion/trabajos/trabajo-1.jpg" alt="Promoción con bandera" loading="lazy">
            <img src="/img/cotizacion/trabajos/trabajo-5.jpg" alt="Promoción con globos" loading="lazy">
            <img src="/img/cotizacion/trabajos/trabajo-4.jpg" alt="Estudiantes con busos Eleven" loading="lazy">
            <img src="/img/cotizacion/trabajos/trabajo-7.jpg" alt="Promoción en el salón" loading="lazy">
            <img class="wide" src="/img/cotizacion/trabajos/trabajo-2.jpg" alt="Promoción con chaquetas vinotinto" loading="lazy">
        </div>
    </div>
</section>

<section class="dark" style="padding-top:40px">
    <div class="wrap">
        <h2 class="d" style="font-size:34px">Colegios que ya <span style="color:var(--y)">visten G'Lauren</span></h2>
        <div class="chips"><span>Universidad Católica</span><span>Colegio La Giralda</span><span>George Williams</span><span>Colegio Montreal</span><span>Generación Enoc</span><span>Liceo Freire</span><span>Liceo Campestre Cristiano</span><span>Liceo ICAM</span><span>Campestre Jaime Garzón</span><span>Liceo Alta Blanca</span><span>Marco Fidel Suárez</span><span>Camino a La Cima</span><span>Campestre San Nicolás de Tolentino</span><span>Colegio ICCK</span><span>Colegio Portillo</span><span>Gimnasio La Arboleda</span><span>IET Cualamán</span><span>Campestre Siquima</span><span>Gimnasio Campestre Cristiano</span><span>San Luis Vitelma</span><span>Instituto Nazareno Modelia</span><span>Colegio Andrés Bello</span><span>Nazareno Cundinamarca</span><span>Colegio Ubaté</span><span>Windsports</span></div>
    </div>
</section>

<section>
    <div class="wrap">
        <div class="kick">Materiales premium</div>
        <h2 class="d">Telas y cremalleras <span class="hl">que usamos</span></h2>
        <div class="brands">
            <div><img src="/img/cotizacion/marcas/lafayette.jpg" alt="Telas Lafayette" style="width:100%;height:100%;object-fit:cover"></div>
            <div style="padding:14px"><img src="/img/cotizacion/marcas/yes.jpg" alt="Cremalleras YES"></div>
            <div><img src="/img/cotizacion/marcas/patprimo.jpg" alt="Telas Patprimo" style="width:150%;max-width:none"></div>
        </div>
        <h3 class="d" style="font-size:24px;margin:26px 0 0">Tecnología en nuestras telas <small style="font-family:Montserrat;font-size:12px;text-transform:none">· según aplique la tela</small></h3>
        <div class="tech"><img src="/img/cotizacion/marcas/laftech-1.jpg" alt="Tecnología Laftech 1" loading="lazy"><img src="/img/cotizacion/marcas/laftech-2.jpg" alt="Tecnología Laftech 2" loading="lazy"><img src="/img/cotizacion/marcas/laftech-3.jpg" alt="Tecnología Laftech 3" loading="lazy"><img src="/img/cotizacion/marcas/laftech-4.jpg" alt="Tecnología Laftech 4" loading="lazy"><img src="/img/cotizacion/marcas/laftech-5.jpg" alt="Tecnología Laftech 5" loading="lazy"><img src="/img/cotizacion/marcas/laftech-6.jpg" alt="Tecnología Laftech 6" loading="lazy"></div>
    </div>
</section>

<section style="padding-top:0">
    <div class="wrap">
        <div class="kick">Catálogo de diseños</div>
        <h2 class="d">Inspírate para tu <span class="hl">diseño</span></h2>
        <p style="margin:0">Diseños de referencia: personalizamos colores, bordados y nombres para tu promoción.</p>

        <div class="cat-h"><h3>Chaqueta con cortes</h3><span>{{ $m($products['estructurada']['price']) }}</span></div>
        <div class="cat"><img class="ph" src="/img/cotizacion/chaqueta.jpg" alt="Estudiante con chaqueta con cortes"><img src="/img/cotizacion/disenos/cortes-1.jpg" alt="Diseño de chaqueta con cortes 1" loading="lazy"><img src="/img/cotizacion/disenos/cortes-2.jpg" alt="Diseño de chaqueta con cortes 2" loading="lazy"><img src="/img/cotizacion/disenos/cortes-3.jpg" alt="Diseño de chaqueta con cortes 3" loading="lazy"><img src="/img/cotizacion/disenos/cortes-4.jpg" alt="Diseño de chaqueta con cortes 4" loading="lazy"><img src="/img/cotizacion/disenos/cortes-5.jpg" alt="Diseño de chaqueta con cortes 5" loading="lazy"><img src="/img/cotizacion/disenos/cortes-6.jpg" alt="Diseño de chaqueta con cortes 6" loading="lazy"><img src="/img/cotizacion/disenos/cortes-7.jpg" alt="Diseño de chaqueta con cortes 7" loading="lazy"><img src="/img/cotizacion/disenos/cortes-8.jpg" alt="Diseño de chaqueta con cortes 8" loading="lazy"></div>

        <div class="cat-h"><h3>Busos</h3><span>{{ $m($products['buso']['price']) }}</span></div>
        <div class="cat"><img class="ph" src="/img/cotizacion/buso.jpg" alt="Estudiante con buso canguro"><img src="/img/cotizacion/disenos/buso-1.jpg" alt="Diseño de buso 1" loading="lazy"><img src="/img/cotizacion/disenos/buso-2.jpg" alt="Diseño de buso 2" loading="lazy"><img src="/img/cotizacion/disenos/buso-3.jpg" alt="Diseño de buso 3" loading="lazy"><img class="ph" src="/img/cotizacion/buso-cremallera.jpg" alt="Estudiante con buso con cremallera"><img src="/img/cotizacion/disenos/buso-4.jpg" alt="Diseño de buso 4" loading="lazy"><img src="/img/cotizacion/disenos/buso-5.jpg" alt="Diseño de buso 5" loading="lazy"><img src="/img/cotizacion/disenos/buso-6.jpg" alt="Diseño de buso 6" loading="lazy"><img src="/img/cotizacion/disenos/buso-7.jpg" alt="Diseño de buso 7" loading="lazy"><img src="/img/cotizacion/disenos/buso-8.jpg" alt="Diseño de buso 8" loading="lazy"></div>

        <div class="cat-h"><h3>Beisboleras</h3><span>{{ $m($products['beisbolera']['price']) }}</span></div>
        <div class="cat"><img class="ph" src="/img/cotizacion/beisbolera.jpg" alt="Estudiante con beisbolera estilo varsity" style="object-position:40% 40%"><img src="/img/cotizacion/disenos/beisbolera-1.jpg" alt="Diseño de beisbolera 1" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-2.jpg" alt="Diseño de beisbolera 2" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-3.jpg" alt="Diseño de beisbolera 3" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-4.jpg" alt="Diseño de beisbolera 4" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-5.jpg" alt="Diseño de beisbolera 5" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-6.jpg" alt="Diseño de beisbolera 6" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-7.jpg" alt="Diseño de beisbolera 7" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-8.jpg" alt="Diseño de beisbolera 8" loading="lazy"><img src="/img/cotizacion/disenos/beisbolera-9.jpg" alt="Diseño de beisbolera 9" loading="lazy"></div>
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

<section>
    <div class="wrap">
        <div class="kick">Cómo trabajamos</div>
        <h2 class="d">Proceso de <span class="hl">elaboración</span></h2>
        <div class="phase"><div class="tab" style="background:var(--k);color:var(--y)"><small>FASE</small><b>01</b><span style="color:#fff">Planeación y aprobación</span></div>
            <div class="steps">
                <div class="step"><i>01</i><b>Diseño y aprobación</b><p>Convertimos la idea de su promoción en una propuesta de diseño y la ajustamos hasta que la aprueben.</p></div>
                <div class="step"><i>02</i><b>Ficha de producción</b><p>Reunimos nombres, tallas y personalizaciones de cada estudiante para producir sin errores.</p></div>
                <div class="step"><i>03</i><b>Pagos 50/50</b><p>50% al firmar el contrato y 50% al finalizar, por transferencia a Bancolombia.</p></div>
            </div></div>
        <div class="phase"><div class="tab" style="background:var(--o)"><small>FASE</small><b>02</b><span>Producción y control</span></div>
            <div class="steps">
                <div class="step"><i>04</i><b>Pedido de telas</b><p>Telas Lafayette o Patprimo y cremalleras YES, según las prendas elegidas.</p></div>
                <div class="step"><i>05</i><b>Corte, confección y bordado</b><p>Compartimos el avance con fotos reales en la plataforma.</p></div>
                <div class="step"><i>06</i><b>Calidad y empaque</b><p>Revisamos costuras, bordados y cremalleras antes de empacar.</p></div>
            </div></div>
        <div class="phase"><div class="tab" style="background:var(--y)"><small>FASE</small><b>03</b><span>Entrega</span></div>
            <div class="steps" style="grid-template-columns:2fr 1fr">
                <div class="step" style="background:var(--k);color:#fff"><i style="background:var(--y);color:var(--k)">07</i><b style="color:var(--y)">Entrega de prendas</b><p style="color:#e2e2e2">Entregamos en el colegio con globos, bandera y fotografía. Más que prendas: un recuerdo para toda la vida.</p></div>
                <div class="step" style="background:var(--o);text-align:center"><b style="font-size:14px">Tiempo total</b><b style="font-size:40px;margin:0">25–35</b><p>días hábiles</p></div>
            </div></div>
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

@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573021106592'));
    $phone = Setting::get('contact_phone', '302 110 6592');
    $email = Setting::get('contact_email', '');
    $address = Setting::get('contact_address', 'Calle 48K 5A-27 sur, Bogotá');
    $hours = Setting::get('contact_hours', 'Lunes a sábado, 8:00 a. m. a 6:00 p. m.');
    $brand = \App\Modules\Chaquetas\Support\Brand::name();
    $sent = session('sent');
    $waLink = 'https://wa.me/'.$wa.'?text='.urlencode('Hola '.$brand.', quiero cotizar dotación / chaquetas para mi empresa.');
    $faqs = collect([
        ['¿Cuánto se demora un pedido?', 'Entre 25 y 35 días hábiles desde que apruebas el diseño y pagas el anticipo del 50%. Si tienes una fecha límite, cuéntanos al cotizar y revisamos el cupo del taller.'],
        ['¿Facturan?', 'Sí. Entregamos factura legal a nombre de tu empresa con su NIT.'],
        ['¿En qué formato envío el logo?', 'Idealmente en PDF, AI, SVG o PNG en buena calidad. Si solo lo tienes en baja resolución, lo redibujamos para el bordado.'],
        ['¿Bordado o estampado?', 'El bordado es más duradero y elegante (ideal para chaquetas, chalecos y polos). El estampado sirve para áreas grandes o muchos colores (camisetas y busos). Te recomendamos según la prenda y el uso.'],
        ['¿Cómo se manejan las tallas del equipo?', 'Te enviamos la tabla de tallas y una plantilla para que cada persona elija la suya. Entregamos empacado y marcado por persona.'],
        ['¿Hacen envíos fuera de Bogotá?', 'Sí, a toda Colombia. En Bogotá también puedes visitar el taller para ver telas y muestras.'],
        ['¿Cómo se paga?', '50% de anticipo para iniciar y 50% contra entrega, por transferencia.'],
        ['¿Hay obsequio?', 'Sí: 1 chaqueta adicional por cada 25 unidades, más una tula de obsequio con tu pedido.'],
    ])->map(fn ($f) => (object) ['question' => $f[0], 'answer' => $f[1]]);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'empresas',
    'seoTitleOverride' => 'Dotación empresarial y chaquetas corporativas con tu logo en Bogotá · G\'Lauren',
    'seoDescriptionOverride' => 'Dotación empresarial en Bogotá y toda Colombia: chaquetas, chalecos, busos, camisas, polos, gorras y delantales con tu logo bordado. Factura legal, precios al por mayor, entrega en 25 a 35 días y 1 chaqueta de obsequio por cada 25.',
    'ogImageOverride' => url('/img/cotizacion/empresas/equipo-1.jpg'),
    'faqs' => $faqs,
    'breadcrumbs' => [['name' => 'Inicio', 'url' => url('/')], ['name' => 'Empresas', 'url' => route('empresas')]],
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.6}
    a{text-decoration:none;color:inherit}
    .wrap{max-width:1140px;margin:0 auto;padding:0 24px}
    nav{display:flex;align-items:center;justify-content:space-between;padding:15px 24px;border-bottom:2px solid #000;background:rgba(13,13,15,.92);position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}
    .brand{display:flex;align-items:center;gap:11px}
    .brand .mk{width:40px;height:40px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:22px;letter-spacing:.04em}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:18px;padding:13px 24px;border:2px solid #000;border-radius:10px;box-shadow:5px 5px 0 #000;cursor:pointer;transition:transform .12s,box-shadow .12s}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503}.btn-wa{background:var(--ok);color:#04240f}.btn-line{background:var(--ink);color:var(--paper);box-shadow:5px 5px 0 var(--gold)}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--gold)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(44px,7vw,86px);line-height:.86;margin:12px 0 18px}
    h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(32px,5vw,54px);line-height:.92;margin:8px 0 26px}
    .gold{color:var(--gold)}
    header.hero{padding:56px 0 46px;border-bottom:2px solid #000;background:radial-gradient(800px 420px at 90% -10%,rgba(233,184,63,.18),transparent 60%)}
    .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center}
    .lead{font-size:19px;color:#cfc9bd;margin-bottom:24px;max-width:560px}
    .cta-row{display:flex;gap:12px;flex-wrap:wrap}
    .hero-pic{position:relative}
    .hero-pic img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:18px;border:3px solid #000;box-shadow:12px 12px 0 var(--gold);display:block}
    .seal{position:absolute;left:-18px;bottom:-18px;background:#ff6b1a;color:#0e0e0e;border:3px solid #000;border-radius:50%;width:112px;height:112px;display:flex;flex-direction:column;align-items:center;justify-content:center;transform:rotate(-8deg);font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:34px;line-height:.9;text-align:center}
    .seal small{font-family:'Hanken Grotesk',sans-serif;font-size:9px;font-weight:800;line-height:1.15;max-width:80px;margin-top:3px}
    section{padding:60px 0}
    .feats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .feat{background:var(--panel);border:2px solid #000;border-radius:14px;padding:20px;box-shadow:6px 6px 0 #000}
    .feat .em{font-size:30px}
    .feat h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:21px;margin:6px 0 4px}
    .feat p{color:var(--muted);font-size:14px}
    .gal{display:grid;grid-template-columns:2fr 1fr;grid-template-rows:220px 220px;gap:14px}
    .gal img{width:100%;height:100%;object-fit:cover;border-radius:14px;border:3px solid #000;display:block}
    .gal .big{grid-row:span 2}
    .gal .white{background:#fff;object-fit:contain}
    .steps{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;counter-reset:s}
    .step{background:var(--panel);border:2px solid #000;border-radius:14px;padding:16px;box-shadow:5px 5px 0 var(--gold)}
    .step:before{counter-increment:s;content:counter(s);display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:var(--gold);color:#1c1503;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:18px;margin-bottom:8px;border:2px solid #000}
    .step b{display:block;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:18px}
    .step span{color:var(--muted);font-size:13px}
    .gift{background:var(--gold);color:#1c1503;border:3px solid #000;border-radius:16px;padding:22px 26px;box-shadow:8px 8px 0 #000;display:flex;gap:20px;align-items:center;flex-wrap:wrap}
    .gift .big{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:64px;line-height:.9}
    .gift b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:26px;line-height:1}
    .form-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:40px;align-items:start}
    form.card{background:var(--panel);border:2px solid #000;border-radius:16px;padding:24px;box-shadow:8px 8px 0 var(--gold);display:grid;grid-template-columns:1fr 1fr;gap:12px}
    form.card .full{grid-column:1/-1}
    label{display:flex;flex-direction:column;gap:5px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
    label small{text-transform:none;letter-spacing:0;font-weight:500}
    input,select,textarea{background:#0f0f13;border:2px solid #000;border-radius:10px;color:var(--paper);padding:11px 12px;font-size:16px;font-family:inherit;font-weight:500;text-transform:none;letter-spacing:0}
    input:focus,select:focus,textarea:focus{outline:none;border-color:var(--gold)}
    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chips label{flex-direction:row;align-items:center;gap:7px;background:#0f0f13;border:2px solid #000;border-radius:999px;padding:8px 13px;font-size:13px;text-transform:none;letter-spacing:0;color:var(--paper);cursor:pointer}
    .chips input{width:16px;height:16px;accent-color:var(--gold);padding:0}
    .ck{flex-direction:row!important;align-items:flex-start;gap:9px!important;text-transform:none!important;letter-spacing:0!important;font-weight:500!important;font-size:13px!important}
    .ck input{width:18px;height:18px;margin-top:2px;accent-color:var(--gold)}
    .err{background:rgba(255,74,61,.12);border:1.5px solid #ff4a3d;color:#ffb0a8;border-radius:10px;padding:10px 12px;font-size:14px}
    .ok{background:rgba(37,211,102,.12);border:2px solid var(--ok);color:#c9f7da;border-radius:14px;padding:22px;text-align:center}
    .ok b{display:block;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:30px;color:var(--ok);margin-bottom:6px}
    .hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
    footer{border-top:2px solid #000;padding:34px 0;color:var(--muted);font-size:13px}
    .navlinks{display:flex;gap:4px;align-items:center}
    .navlinks a.l{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:15px;padding:8px 12px;border-radius:8px;color:var(--paper)}
    .navlinks a.l:hover{color:var(--gold)}
    .trust{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
    .trust span{display:inline-flex;align-items:center;gap:6px;background:rgba(244,239,227,.06);border:1.5px solid var(--line);border-radius:999px;padding:6px 12px;font-size:13.5px;font-weight:600}
    .cat{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .item{background:var(--panel);border:2px solid #000;border-radius:14px;overflow:hidden;box-shadow:6px 6px 0 #000;display:flex;flex-direction:column}
    .item .ph{height:150px;background:#24242c center/cover no-repeat;display:grid;place-items:center;font-size:54px;border-bottom:2px solid #000}
    .item .bd{padding:14px 16px}
    .item h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;margin-bottom:4px}
    .item p{color:var(--muted);font-size:13.5px}
    .law{display:grid;grid-template-columns:1.1fr .9fr;gap:30px;align-items:center;background:var(--panel);border:2px solid #000;border-radius:18px;padding:30px;box-shadow:10px 10px 0 var(--gold)}
    .dates{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
    .date{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:12px;padding:14px;text-align:center}
    .date b{display:block;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:34px;line-height:1}
    .date small{font-weight:700;font-size:12px}
    .who{display:flex;flex-wrap:wrap;gap:10px}
    .who span{background:var(--panel);border:2px solid #000;border-radius:12px;padding:12px 16px;font-weight:700;box-shadow:4px 4px 0 #000}
    details{background:var(--panel);border:2px solid #000;border-radius:12px;padding:14px 16px;margin-bottom:10px}
    summary{cursor:pointer;font-weight:700;font-size:16px}
    details p{color:#cfc9bd;margin-top:8px;font-size:15px}
    .contact{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .ch{background:var(--panel);border:2px solid #000;border-radius:14px;padding:18px;box-shadow:6px 6px 0 #000;display:flex;flex-direction:column;gap:6px}
    .ch .ic{font-size:30px}
    .ch b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:19px}
    .ch span{color:var(--muted);font-size:14px;word-break:break-word}
    .ch a.go{margin-top:auto;color:var(--gold);font-weight:700;font-size:14px}
    .fab{position:fixed;right:18px;bottom:18px;z-index:50;background:var(--ok);color:#04240f;border:2px solid #000;border-radius:999px;padding:13px 18px;font-weight:800;box-shadow:4px 4px 0 #000;display:flex;align-items:center;gap:8px;font-size:15px}
    .foot{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:24px;text-align:left}
    .foot h4{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;color:var(--paper);font-size:17px;margin-bottom:8px}
    .foot a{display:block;color:var(--muted);margin-bottom:4px}
    .foot a:hover{color:var(--gold)}
    @media(max-width:900px){.navlinks a.l{display:none}.cat,.contact{grid-template-columns:1fr 1fr}.law,.foot{grid-template-columns:1fr}.hero-grid,.form-grid{grid-template-columns:1fr}.feats{grid-template-columns:1fr 1fr}.steps{grid-template-columns:1fr 1fr}.gal{grid-template-columns:1fr;grid-template-rows:auto}.gal img{height:220px}.gal .big{grid-row:auto}}
    @media(max-width:560px){.cat,.contact,.dates{grid-template-columns:1fr}.feats,.steps{grid-template-columns:1fr}form.card{grid-template-columns:1fr}.seal{width:88px;height:88px;font-size:26px;left:-8px}}
</style>
</head>
<body>
<nav>
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">{{ $brand }}</span></a>
    <div class="navlinks">
        <a href="#prendas" class="l">Prendas</a>
        <a href="#dotacion" class="l">Dotación de ley</a>
        <a href="#proceso" class="l">Proceso</a>
        <a href="#preguntas" class="l">Preguntas</a>
        <a href="#contacto" class="l">Contacto</a>
        <a href="#cotizar" class="btn btn-gold" style="font-size:15px;padding:10px 18px;margin-left:6px">Cotizar</a>
    </div>
</nav>

<header class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="ey">// Empresas y dotación</span>
            <h1>Tu equipo, <span class="gold">con tu marca</span></h1>
            <p class="lead">Dotación empresarial y prendas de marca: chaquetas, chalecos, busos, camisas, polos, gorras y delantales con el logo de tu empresa bordado o estampado. Taller propio en Bogotá, precios al por mayor y factura legal.</p>
            <div class="cta-row">
                <a href="#cotizar" class="btn btn-gold">Pedir cotización →</a>
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-wa">WhatsApp</a>
                <a href="tel:{{ preg_replace('/\D/', '', $phone) }}" class="btn btn-line">Llamar</a>
            </div>
            <div class="trust">
                <span>🏭 Taller propio en Bogotá</span>
                <span>🧾 Factura legal</span>
                <span>🇨🇴 Envíos a toda Colombia</span>
                <span>⏱️ 25 a 35 días hábiles</span>
            </div>
        </div>
        <div class="hero-pic">
            <img src="/img/cotizacion/empresas/equipo-1.jpg" alt="Equipo de trabajo con chaquetas corporativas bordadas">
            <div class="seal">1×25<small>CHAQUETA GRATIS</small></div>
        </div>
    </div>
</header>

<section>
    <div class="wrap">
        <span class="ey">// Por qué G'Lauren</span>
        <h2>Prendas que <span class="gold">representan</span> tu empresa</h2>
        <div class="feats">
            <div class="feat"><div class="em">🧵</div><h3>Tu logo bordado</h3><p>Bordado o estampado con los colores exactos de tu marca, en el pecho, la espalda o la manga.</p></div>
            <div class="feat"><div class="em">🧥</div><h3>Telas premium</h3><p>Lafayette y Patprimo, cremalleras YES. Prendas hechas para el uso diario.</p></div>
            <div class="feat"><div class="em">💲</div><h3>Precios al por mayor</h3><p>Cotización por unidad según cantidades, con cada prenda en foto y sus características.</p></div>
            <div class="feat"><div class="em">🚚</div><h3>25 a 35 días</h3><p>Días hábiles desde la aprobación del diseño y el anticipo.</p></div>
            <div class="feat"><div class="em">🛡️</div><h3>Garantía</h3><p>Por defectos de fabricación: confección y cremalleras.</p></div>
            <div class="feat"><div class="em">💳</div><h3>Pago 50 / 50</h3><p>50% para iniciar y 50% contra entrega.</p></div>
        </div>
    </div>
</section>

<section id="prendas" style="scroll-margin-top:80px">
    <div class="wrap">
        <span class="ey">// Qué fabricamos</span>
        <h2>Prendas para tu <span class="gold">equipo</span></h2>
        <div class="cat">
            <div class="item"><div class="ph" style="background-image:url('/img/cotizacion/empresas/equipo-2.jpg')"></div><div class="bd"><h3>Chaquetas corporativas</h3><p>Impermeables, acolchadas o en Lafayette, con logo bordado y reflectivos si los necesitas.</p></div></div>
            <div class="item"><div class="ph">🦺</div><div class="bd"><h3>Chalecos</h3><p>Para campo, obra, eventos o atención al cliente. Con bolsillos, reflectivos y tu marca.</p></div></div>
            <div class="item"><div class="ph" style="background-image:url('/img/cotizacion/buso-cremallera.jpg');background-position:50% 30%"></div><div class="bd"><h3>Busos y hoodies</h3><p>Con cremallera, cuello o canguro. Ideales para equipos, eventos y temporadas de frío.</p></div></div>
            <div class="item"><div class="ph">👔</div><div class="bd"><h3>Camisas</h3><p>Camisas de uniforme para el personal administrativo y de atención, con logo bordado.</p></div></div>
            <div class="item"><div class="ph">👕</div><div class="bd"><h3>Polos y camisetas</h3><p>Polos tipo lacoste y camisetas deportivas para el día a día, ferias y activaciones.</p></div></div>
            <div class="item"><div class="ph">🧢</div><div class="bd"><h3>Gorras</h3><p>Con tu logo bordado al frente y detalles en los colores de la marca.</p></div></div>
            <div class="item"><div class="ph">🧑‍🍳</div><div class="bd"><h3>Delantales</h3><p>Para restaurantes, cafés, panaderías y tiendas: resistentes y con tu marca.</p></div></div>
            <div class="item"><div class="ph">📦</div><div class="bd"><h3>Dotación completa</h3><p>Armamos el kit de cada cargo y lo entregamos empacado y marcado por persona.</p></div></div>
        </div>
        <div style="margin-top:30px">
            <span class="ey">// Para quién</span>
            <div class="who" style="margin-top:12px">
                <span>🏢 Empresas y oficinas</span><span>🏗️ Constructoras</span><span>🚚 Logística y transporte</span><span>🍽️ Restaurantes y cafés</span>
                <span>🏥 Clínicas y consultorios</span><span>🛍️ Tiendas y comercio</span><span>⚽ Equipos deportivos</span><span>🎤 Eventos y activaciones</span>
            </div>
        </div>
    </div>
</section>

<section id="dotacion" style="scroll-margin-top:80px">
    <div class="wrap">
        <div class="law">
            <div>
                <span class="ey">// Dotación de ley</span>
                <h2 style="margin-bottom:14px">Cumple con la dotación <span class="gold">sin complicarte</span></h2>
                <p style="color:#cfc9bd;margin-bottom:10px">En Colombia, el artículo 230 del Código Sustantivo del Trabajo obliga a entregar dotación (vestido y calzado de labor) <b>tres veces al año</b> a los trabajadores que ganan hasta dos salarios mínimos y llevan más de tres meses en la empresa.</p>
                <p style="color:#cfc9bd;margin-bottom:18px">Nosotros te ayudamos con la parte del vestido: guardamos las tallas de tu equipo, programamos las tres entregas y te las tenemos listas, empacadas por persona y con factura.</p>
                <a href="#cotizar" class="btn btn-gold">Programar mi dotación</a>
            </div>
            <div class="dates">
                <div class="date"><b>30</b><small>DE ABRIL</small></div>
                <div class="date"><b>31</b><small>DE AGOSTO</small></div>
                <div class="date"><b>20</b><small>DE DICIEMBRE</small></div>
            </div>
        </div>
    </div>
</section>

<section style="padding-top:10px">
    <div class="wrap">
        <div class="gal">
            <img class="big" src="/img/cotizacion/empresas/equipo-2.jpg" alt="Equipo con chaquetas corporativas" loading="lazy">
            <img src="/img/cotizacion/empresas/equipo-1.jpg" alt="Chaquetas corporativas azules" loading="lazy" style="object-position:50% 30%">
            <img class="white" src="/img/cotizacion/empresas/chaqueta-1.jpg" alt="Chaqueta corporativa con reflectivos" loading="lazy">
        </div>
    </div>
</section>

<section style="padding-top:20px">
    <div class="wrap">
        <div class="gift">
            <div class="big">1×25</div>
            <div style="flex:1;min-width:240px"><b>1 chaqueta adicional de obsequio por cada 25 unidades</b><div style="font-weight:600;margin-top:6px">+ tula de obsequio con tu pedido.</div></div>
            <a href="#cotizar" class="btn btn-line">Quiero mi cotización</a>
        </div>
    </div>
</section>

<section id="proceso" style="scroll-margin-top:80px">
    <div class="wrap">
        <span class="ey">// Cómo trabajamos</span>
        <h2>Del sí a la <span class="gold">entrega</span></h2>
        <div class="steps">
            <div class="step"><b>Cotización</b><span>Te enviamos cada prenda con foto, características y valor por unidad.</span></div>
            <div class="step"><b>Anticipo 50%</b><span>Con el anticipo separamos telas y arrancamos.</span></div>
            <div class="step"><b>Diseño y tallas</b><span>Diseño con tu logo y tabla de tallas del equipo.</span></div>
            <div class="step"><b>Confección</b><span>Corte, bordado o estampado y control de calidad.</span></div>
            <div class="step"><b>Entrega</b><span>Empacado por persona; se paga el saldo.</span></div>
        </div>
    </div>
</section>

<section id="preguntas" style="scroll-margin-top:80px">
    <div class="wrap">
        <span class="ey">// Preguntas frecuentes</span>
        <h2>Lo que más nos <span class="gold">preguntan</span></h2>
        @foreach($faqs as $f)
            <details {{ $loop->first ? 'open' : '' }}><summary>{{ $f->question }}</summary><p>{{ $f->answer }}</p></details>
        @endforeach
    </div>
</section>

<section id="contacto" style="scroll-margin-top:80px">
    <div class="wrap">
        <span class="ey">// Contacto</span>
        <h2>Hablemos de tu <span class="gold">pedido</span></h2>
        <div class="contact">
            <div class="ch"><div class="ic">💬</div><b>WhatsApp</b><span>La forma más rápida: te respondemos el mismo día.</span><a class="go" href="{{ $waLink }}" target="_blank" rel="noopener">Escribir ahora →</a></div>
            <div class="ch"><div class="ic">📞</div><b>Llámanos</b><span>{{ $phone }}</span><a class="go" href="tel:{{ preg_replace('/\D/', '', $phone) }}">Llamar →</a></div>
            @if($email)
                <div class="ch"><div class="ic">✉️</div><b>Correo</b><span>{{ $email }}</span><a class="go" href="mailto:{{ $email }}?subject={{ rawurlencode('Cotización de dotación empresarial') }}">Enviar correo →</a></div>
            @else
                <div class="ch"><div class="ic">📝</div><b>Formulario</b><span>Déjanos los datos y te enviamos la cotización con fotos.</span><a class="go" href="#cotizar">Ir al formulario →</a></div>
            @endif
            <div class="ch"><div class="ic">📍</div><b>Visita el taller</b><span>{{ $address }}<br>{{ $hours }}</span><a class="go" href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address.', Colombia') }}" target="_blank" rel="noopener">Cómo llegar →</a></div>
        </div>
    </div>
</section>

<section id="cotizar" style="scroll-margin-top:80px">
    <div class="wrap form-grid">
        <div>
            <span class="ey">// Cotiza sin compromiso</span>
            <h2>Pide tu <span class="gold">cotización</span></h2>
            <p class="lead" style="font-size:17px">Cuéntanos qué necesitas y te enviamos la cotización con fotos, características y precios al por mayor lo antes posible.</p>
            <p style="color:var(--muted);font-size:14px">¿Prefieres hablar? Escríbenos al <a href="https://wa.me/{{ $wa }}" class="gold" target="_blank" rel="noopener">{{ $phone }}</a>.</p>
        </div>

        @if($sent)
            <div class="ok">
                <b>¡Recibimos tu solicitud!</b>
                Tu número de cotización es <strong>{{ $sent }}</strong>. Un asesor la prepara y te la envía por WhatsApp o correo muy pronto.
                <div style="margin-top:14px"><a href="{{ route('home') }}" class="btn btn-line" style="font-size:15px">Volver al inicio</a></div>
            </div>
        @else
        <form method="POST" action="{{ route('empresas.store') }}" enctype="multipart/form-data" class="card">
            @csrf
            <div class="hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            @if($errors->any())<div class="err full">⚠ {{ $errors->first() }}</div>@endif
            <label class="full">Empresa<input name="company" value="{{ old('company') }}" required maxlength="160" placeholder="Ej.: Constructora Andes S.A.S."></label>
            <label>NIT <small>(opcional)</small><input name="nit" value="{{ old('nit') }}" maxlength="40"></label>
            <label>Ciudad<input name="city" value="{{ old('city') }}" maxlength="120" placeholder="Bogotá"></label>
            <label>Tu nombre<input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"></label>
            <label>Cargo <small>(opcional)</small><input name="role" value="{{ old('role') }}" maxlength="120" placeholder="Compras, RR. HH.…"></label>
            <label>Celular / WhatsApp<input name="phone" value="{{ old('phone') }}" required maxlength="20" inputmode="tel" autocomplete="tel"></label>
            <label>Correo <small>(opcional)</small><input type="email" name="email" value="{{ old('email') }}" maxlength="160" autocomplete="email"></label>
            <div class="full"><label style="margin-bottom:6px">¿Qué prendas necesitas?</label>
                <div class="chips">
                    @foreach($garments as $g)
                        <label><input type="checkbox" name="garments[]" value="{{ $g }}" {{ in_array($g, old('garments', ['Chaquetas']), true) ? 'checked' : '' }}> {{ $g }}</label>
                    @endforeach
                </div>
            </div>
            <label>Unidades aprox.<input type="number" name="units" value="{{ old('units', 25) }}" min="1" max="100000" required></label>
            <label>Tu logo <small>(opcional)</small><input type="file" name="logo" accept="image/*,.pdf,.svg" style="padding:8px"></label>
            <label class="full">Cuéntanos más <small>(colores, uso, fecha que necesitas…)</small><textarea name="message" rows="3" maxlength="2000">{{ old('message') }}</textarea></label>
            <label class="ck full"><input type="checkbox" name="accept" value="1" {{ old('accept', '1') ? 'checked' : '' }} required> Acepto el tratamiento de mis datos para recibir la cotización.</label>
            <button class="btn btn-gold full">Enviar solicitud →</button>
        </form>
        @endif
    </div>
</section>

<footer>
    <div class="wrap foot">
        <div>
            <a href="{{ route('home') }}" class="brand" style="display:flex;margin-bottom:10px"><span class="mk">G</span><span class="wm" style="color:var(--paper)">{{ $brand }}</span></a>
            <p>Dotación empresarial, chaquetas corporativas y chaquetas de prom. Taller propio en Bogotá.</p>
            <p style="margin-top:8px">NIT 1033779133-6 · {{ $address }} · {{ $phone }}</p>
        </div>
        <div>
            <h4>Empresas</h4>
            <a href="#prendas">Prendas</a><a href="#dotacion">Dotación de ley</a><a href="#proceso">Cómo trabajamos</a><a href="#cotizar">Cotizar</a>
        </div>
        <div>
            <h4>{{ $brand }}</h4>
            <a href="{{ route('home') }}">Chaquetas de prom</a><a href="{{ route('ciudades.index') }}">Chaquetas por ciudad</a><a href="{{ route('news.index') }}">Blog</a><a href="{{ $waLink }}" target="_blank" rel="noopener">WhatsApp</a>
        </div>
    </div>
</footer>
<a href="{{ $waLink }}" class="fab" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">💬 WhatsApp</a>
@if($errors->any() || $sent)
<script>document.getElementById('cotizar').scrollIntoView();</script>
@endif
</body>
</html>

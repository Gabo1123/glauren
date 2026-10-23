<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    $wa = preg_replace('/\D/','', \App\Modules\Chaquetas\Models\Setting::get('contact_whatsapp','573021106592'));
    $waMsg = fn($t) => 'https://wa.me/'.$wa.'?text='.urlencode($t);
    $allKeywords = collect($categories)->flatMap(fn($c)=>array_filter([$c->keywords]))->implode(', ');
    $heroPics = $gallery->take(5);
@endphp
@include('partials.seo', [
    'seoKey' => 'fotografia',
    'seoTitleOverride' => 'Fotografía de Graduación en Colombia · Fotos de Grado, Togas y Anuarios | Glauren',
    'seoDescriptionOverride' => 'Fotografía profesional de grados para colegios y universidades: fotos de grado, cubrimiento de ceremonia, anuarios, alquiler de togas y birretes, diplomas y souvenirs. Cotiza por WhatsApp.',
    'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>'Fotografía de grados','url'=>url('/fotografia-de-grados')]],
])
<meta name="keywords" content="fotografía de graduación, fotos de grado, fotos de graduación, anuario escolar, alquiler de togas y birretes, estolas de grado, diplomas, actas de grado, mosaico de grado, paquetes de grado, fotógrafo de grados{{ $allKeywords ? ', '.$allKeywords : '' }}">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script type="application/ld+json">{!! json_encode([
    '@context'=>'https://schema.org','@type'=>'LocalBusiness','name'=>'Glauren · Fotografía de Grados',
    'image'=>$heroPics->first()?->url(),'url'=>url('/fotografia-de-grados'),
    'telephone'=>'+'.$wa,'areaServed'=>'Colombia',
    'description'=>'Fotografía profesional de graduación, anuarios, togas y birretes, diplomas y souvenirs para colegios y universidades.',
    'priceRange'=>'$$',
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>[
        ['@type'=>'Question','name'=>'¿Hacen fotos en el colegio o en estudio?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Ambas: sesión pre-ceremonia en estudio con iluminación profesional y cubrimiento de la ceremonia y fotos de grupo en tu colegio.']],
        ['@type'=>'Question','name'=>'¿Alquilan togas y birretes?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Sí, alquilamos togas y birretes según la talla de cada estudiante y elaboramos estolas bordadas con el escudo de la institución.']],
        ['@type'=>'Question','name'=>'¿Atienden otras ciudades?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Sí, cubrimos graduaciones en toda Colombia. Escríbenos por WhatsApp con tu ciudad y número de estudiantes.']],
    ],
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<style>
    :root{ --ink:#0e0d0c; --ink2:#15130f; --panel:#1b1917; --line:rgba(255,255,255,.09); --bone:#f4f1ea; --muted:#a79f97; --gold:#c9a54b; --gold2:#e6cf86; }
    *{ box-sizing:border-box; margin:0; padding:0 }
    html{ scroll-behavior:smooth }
    body{ background:var(--ink); color:var(--bone); font-family:'Hanken Grotesk',system-ui,sans-serif; line-height:1.6; overflow-x:hidden }
    .wrap{ max-width:1160px; margin:0 auto; padding:0 20px }
    h1,h2,h3,h4{ font-family:'Big Shoulders Display',sans-serif; line-height:1.03; text-transform:uppercase; letter-spacing:-.01em }
    a{ color:inherit; text-decoration:none }
    .eyebrow{ color:var(--gold); font-weight:700; letter-spacing:.16em; text-transform:uppercase; font-size:12.5px }
    .btn{ display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:13px 24px; font-weight:700; font-size:15px; border:0; cursor:pointer; transition:transform .15s,box-shadow .2s }
    .btn:hover{ transform:translateY(-2px) }
    .btn-wa{ background:#25d366; color:#04240f }
    .btn-gold{ background:linear-gradient(135deg,var(--gold),var(--gold2)); color:#1a1405; box-shadow:0 8px 24px rgba(201,165,75,.3) }
    .btn-ghost{ border:1px solid var(--line); color:var(--bone); background:rgba(255,255,255,.03) }
    /* nav */
    .nav{ position:sticky; top:0; z-index:40; background:rgba(14,13,12,.82); backdrop-filter:blur(12px); border-bottom:1px solid var(--line) }
    .nav .wrap{ display:flex; align-items:center; gap:22px; height:66px }
    .nav .brand{ font-family:'Big Shoulders Display',sans-serif; font-weight:900; text-transform:uppercase; letter-spacing:.05em; font-size:22px; color:var(--bone) }
    .nav .brand b{ color:var(--gold) }
    .nav .links{ display:flex; gap:22px; margin-left:12px }
    .nav .links a{ color:var(--muted); font-size:14px; font-weight:600 }
    .nav .links a:hover{ color:var(--bone) }
    .nav .sp{ margin-left:auto }
    @media(max-width:820px){ .nav .links{ display:none } }
    /* hero */
    .hero{ position:relative; padding:64px 0 60px; background:radial-gradient(900px 420px at 78% -5%,rgba(201,165,75,.20),transparent 60%),linear-gradient(130deg,#141210,#1c1710) }
    .hero .wrap{ display:grid; grid-template-columns:1.1fr .9fr; gap:40px; align-items:center }
    @media(max-width:900px){ .hero .wrap{ grid-template-columns:1fr; gap:24px } }
    .hero h1{ font-size:clamp(40px,7vw,76px); font-weight:900 }
    .hero h1 span{ color:var(--gold) }
    .hero p.lead{ color:var(--muted); font-size:18px; max-width:540px; margin:18px 0 26px }
    .cta-row{ display:flex; gap:12px; flex-wrap:wrap }
    .trust{ display:flex; gap:22px; margin-top:28px; flex-wrap:wrap }
    .trust .t{ font-size:13px; color:var(--muted) } .trust .t b{ color:var(--bone); font-family:'Big Shoulders Display',sans-serif; font-size:22px; display:block }
    /* camera */
    .cam-stage{ position:relative; display:grid; place-items:center; min-height:320px }
    .cam{ position:relative; width:230px; height:168px; background:linear-gradient(160deg,#2a2622,#191714); border:1px solid #3a342c; border-radius:22px; box-shadow:0 30px 60px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.06); animation:floaty 5s ease-in-out infinite }
    .cam::before{ content:''; position:absolute; top:-14px; left:34px; width:70px; height:18px; background:#201d19; border:1px solid #3a342c; border-radius:8px 8px 0 0 }
    .cam .flash-bulb{ position:absolute; top:-10px; right:26px; width:26px; height:14px; background:#43evaluated; background:#4a4views; background:#45413a; border-radius:4px }
    .cam .lens{ position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:112px; height:112px; border-radius:50%; background:radial-gradient(circle at 38% 34%, #6a645b 0 8%, #211f1b 42%, #0c0b0a 70%); border:6px solid #2d2823; box-shadow:0 0 0 3px #15130f, inset 0 0 22px rgba(0,0,0,.8) }
    .cam .lens::after{ content:''; position:absolute; top:22%; left:24%; width:22px; height:22px; border-radius:50%; background:radial-gradient(circle,#fff,rgba(255,255,255,0) 70%); opacity:.7 }
    .cam .ring{ position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:130px; height:130px; border-radius:50%; border:2px dashed rgba(201,165,75,.5); animation:spin 9s linear infinite }
    .cam .rec{ position:absolute; bottom:12px; left:16px; width:9px; height:9px; border-radius:50%; background:var(--gold); box-shadow:0 0 10px var(--gold); animation:blink 1.4s steps(1) infinite }
    .shutter{ margin-top:20px }
    .flash-overlay{ position:fixed; inset:0; background:#fff; opacity:0; pointer-events:none; z-index:999 }
    .flash-overlay.go{ animation:flash .5s ease-out }
    @keyframes flash{ 0%{opacity:0} 10%{opacity:.9} 100%{opacity:0} }
    @keyframes floaty{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    @keyframes spin{ to{ transform:translate(-50%,-50%) rotate(360deg) } }
    @keyframes blink{ 0%,50%{opacity:1} 51%,100%{opacity:.2} }
    /* floating pics around camera */
    .float-pic{ position:absolute; width:86px; height:112px; object-fit:cover; border-radius:10px; border:2px solid rgba(255,255,255,.12); box-shadow:0 14px 30px rgba(0,0,0,.5) }
    .fp1{ top:6px; left:2%; transform:rotate(-8deg); animation:floaty 6s ease-in-out infinite }
    .fp2{ bottom:4px; right:2%; transform:rotate(7deg); width:96px; height:122px; animation:floaty 7s ease-in-out .4s infinite }
    @media(max-width:900px){ .float-pic{ display:none } }
    /* sections */
    section{ padding:60px 0 }
    .sec-head{ max-width:660px; margin:0 auto 36px; text-align:center }
    .sec-head h2{ font-size:clamp(30px,5vw,48px); font-weight:800 }
    .sec-head p{ color:var(--muted); margin-top:12px; font-size:17px }
    /* steps */
    .steps{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px } @media(max-width:760px){ .steps{ grid-template-columns:1fr } }
    .step{ background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:26px }
    .step .n{ width:42px; height:42px; border-radius:12px; background:rgba(201,165,75,.14); color:var(--gold); display:grid; place-items:center; font-family:'Big Shoulders Display',sans-serif; font-size:22px; font-weight:800; margin-bottom:14px }
    .step h4{ font-size:20px } .step p{ color:var(--muted); font-size:14.5px; margin-top:6px }
    /* servicios */
    .cat{ margin-bottom:40px }
    .cat-h{ display:flex; align-items:baseline; gap:12px; border-bottom:1px solid var(--line); padding-bottom:12px; margin-bottom:20px; flex-wrap:wrap }
    .cat-h h3{ font-size:25px; font-weight:800 } .cat-h .s{ color:var(--muted); font-size:14px }
    .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:14px }
    .svc{ background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:18px 18px; transition:border-color .2s,transform .15s }
    .svc:hover{ border-color:rgba(201,165,75,.5); transform:translateY(-3px) }
    .svc h4{ font-size:18px } .svc .st{ color:var(--gold); font-size:12.5px; font-weight:700; margin:3px 0 7px; text-transform:uppercase; letter-spacing:.03em }
    .svc p{ color:var(--muted); font-size:14px }
    /* portafolio */
    .gal-wrap{ background:linear-gradient(180deg,#141210,#17130d) }
    .filters{ display:flex; gap:8px; flex-wrap:wrap; justify-content:center; margin-bottom:24px }
    .filters button{ background:var(--panel); border:1px solid var(--line); color:var(--muted); border-radius:999px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer }
    .filters button.on{ background:var(--gold); color:#1a1405; border-color:var(--gold) }
    .masonry{ columns:4 240px; column-gap:12px } @media(max-width:600px){ .masonry{ columns:2 150px } }
    .masonry .ph{ break-inside:avoid; margin-bottom:12px; border-radius:12px; overflow:hidden; border:1px solid var(--line); cursor:pointer; position:relative }
    .masonry .ph img{ width:100%; display:block; transition:transform .4s } .masonry .ph:hover img{ transform:scale(1.05) }
    .masonry .ph .cap{ position:absolute; left:0; right:0; bottom:0; padding:18px 10px 8px; font-size:12px; background:linear-gradient(transparent,rgba(0,0,0,.75)); opacity:0; transition:.2s } .masonry .ph:hover .cap{ opacity:1 }
    /* paquetes */
    .pks{ display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px }
    .pk{ background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:28px; display:flex; flex-direction:column }
    .pk.feat{ border-color:var(--gold); box-shadow:0 0 0 1px var(--gold) inset, 0 20px 50px rgba(201,165,75,.12) }
    .pk .tag{ font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.09em; color:var(--gold); margin-bottom:8px }
    .pk h3{ font-size:26px } .pk .line{ color:var(--muted); font-size:14px; margin:4px 0 16px }
    .pk ul{ list-style:none; display:flex; flex-direction:column; gap:9px; margin-bottom:22px }
    .pk li{ font-size:14.5px; padding-left:26px; position:relative } .pk li::before{ content:'✓'; position:absolute; left:0; color:var(--gold); font-weight:800 }
    .pk .btn{ margin-top:auto; justify-content:center }
    /* faq */
    .faq details{ border:1px solid var(--line); border-radius:12px; padding:16px 20px; margin-bottom:10px; background:var(--panel) }
    .faq summary{ cursor:pointer; font-weight:700; font-size:16.5px; list-style:none; display:flex; justify-content:space-between }
    .faq summary::after{ content:'+'; color:var(--gold); font-size:22px } .faq details[open] summary::after{ content:'–' }
    .faq p{ color:var(--muted); margin-top:12px; font-size:15px }
    /* contacto */
    .contact-wrap{ background:radial-gradient(700px 300px at 50% -10%,rgba(201,165,75,.16),transparent 60%) }
    .contact{ display:grid; grid-template-columns:1fr 1fr; gap:28px; background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:34px } @media(max-width:800px){ .contact{ grid-template-columns:1fr } }
    .contact h2{ font-size:34px; font-weight:800 } .contact .sub{ color:var(--muted); margin:10px 0 18px }
    .contact .field{ margin-bottom:12px } .contact label{ font-size:13px; color:var(--muted); display:block; margin-bottom:4px }
    .contact input,.contact textarea{ width:100%; background:var(--ink); border:1px solid var(--line); color:var(--bone); border-radius:10px; padding:11px 14px; font-size:15px }
    .contact input:focus,.contact textarea:focus{ outline:none; border-color:var(--gold) }
    .okmsg{ background:rgba(37,211,102,.12); border:1px solid rgba(37,211,102,.4); color:#9be7b6; border-radius:10px; padding:12px 14px; margin-bottom:14px }
    .wa-card{ display:flex; flex-direction:column; justify-content:center; gap:14px }
    .wa-card .big{ font-size:clamp(26px,4vw,40px); font-weight:800 }
    /* floating button */
    .float-btn{ position:fixed; right:18px; bottom:18px; z-index:60; display:flex; gap:10px; align-items:center; background:#25d366; color:#04240f; font-weight:800; padding:14px 20px; border-radius:999px; box-shadow:0 12px 30px rgba(37,211,102,.4); animation:pulse 2s infinite }
    @keyframes pulse{ 0%{ box-shadow:0 0 0 0 rgba(37,211,102,.5) } 70%{ box-shadow:0 0 0 16px rgba(37,211,102,0) } 100%{ box-shadow:0 0 0 0 rgba(37,211,102,0) } }
    footer{ border-top:1px solid var(--line); padding:40px 0; color:var(--muted); font-size:14px; text-align:center }
    /* lightbox */
    .lb{ position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:200; display:none; place-items:center; padding:20px } .lb.on{ display:grid }
    .lb img{ max-width:92vw; max-height:88vh; border-radius:10px } .lb .x{ position:absolute; top:18px; right:24px; font-size:34px; color:#fff; cursor:pointer }

    /* ===== Premium & dinámico ===== */
    /* grano de cine */
    body::after{ content:''; position:fixed; inset:0; pointer-events:none; z-index:1; opacity:.04;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E") }
    .nav,.hero,section,footer,.contact-wrap,.gal-wrap{ position:relative; z-index:2 }
    /* aurora animada en hero */
    .hero::before{ content:''; position:absolute; inset:0; z-index:0; pointer-events:none;
        background:
          radial-gradient(500px 300px at 15% 20%, rgba(201,165,75,.18), transparent 60%),
          radial-gradient(480px 320px at 85% 70%, rgba(120,90,200,.14), transparent 60%),
          radial-gradient(420px 260px at 60% 10%, rgba(37,211,102,.08), transparent 60%);
        filter:blur(6px); animation:aurora 14s ease-in-out infinite alternate }
    .hero .wrap{ position:relative; z-index:2 }
    @keyframes aurora{ 0%{ transform:translate3d(0,0,0) scale(1) } 50%{ transform:translate3d(-20px,14px,0) scale(1.08) } 100%{ transform:translate3d(18px,-10px,0) scale(1.04) } }
    /* titulo con degradado sutil */
    .hero h1 span{ background:linear-gradient(120deg,var(--gold),var(--gold2) 60%,#fff); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent }
    /* reveal al hacer scroll */
    .rv{ opacity:0; transform:translateY(26px); transition:opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1) }
    .rv.in{ opacity:1; transform:none }
    @media(prefers-reduced-motion:reduce){ .rv{ opacity:1; transform:none; transition:none } .hero::before,.cam{ animation:none } }
    /* cámara parallax */
    .cam{ transition:transform .2s ease-out }
    .cam .lens{ box-shadow:0 0 0 3px #15130f, inset 0 0 22px rgba(0,0,0,.8), 0 0 40px rgba(201,165,75,.25) }
    /* marquee */
    .marquee{ border-top:1px solid var(--line); border-bottom:1px solid var(--line); background:#121009; overflow:hidden; padding:14px 0 }
    .marquee .track{ display:inline-flex; gap:40px; white-space:nowrap; animation:scrollx 26s linear infinite; will-change:transform }
    .marquee span{ font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; letter-spacing:.06em; font-size:18px; color:var(--muted) }
    .marquee span b{ color:var(--gold) }
    @keyframes scrollx{ to{ transform:translateX(-50%) } }
    /* tarjetas premium: brillo dorado en hover */
    .step,.svc,.pk{ position:relative; overflow:hidden; background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.01)) , var(--panel) }
    .step::before,.svc::before{ content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,var(--gold),transparent); opacity:0; transition:.3s }
    .step:hover::before,.svc:hover::before{ opacity:1 }
    .step:hover,.svc:hover{ box-shadow:0 20px 40px rgba(0,0,0,.4) }
    /* badge grande de categoría */
    .cat-ico{ width:46px; height:46px; border-radius:13px; background:linear-gradient(135deg,rgba(201,165,75,.22),rgba(201,165,75,.05)); border:1px solid rgba(201,165,75,.3); display:inline-grid; place-items:center; font-size:22px; margin-right:4px }
    /* testimonios */
    .tst{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px } @media(max-width:800px){ .tst{ grid-template-columns:1fr } }
    .tcard{ background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:24px }
    .tcard .stars{ color:var(--gold); letter-spacing:2px; margin-bottom:8px }
    .tcard p{ font-size:15px } .tcard .who{ color:var(--muted); font-size:13px; margin-top:12px; font-weight:600 }
</style>
</head>
<body>

<div class="flash-overlay" id="flash"></div>

<nav class="nav"><div class="wrap">
    <a href="{{ url('/') }}" class="brand">GLAUREN<b>·FOTO</b></a>
    <div class="links">
        <a href="#servicios">Servicios</a>
        <a href="#galeria">Galería</a>
        <a href="#paquetes">Paquetes</a>
        <a href="#contacto">Contacto</a>
    </div>
    <a class="sp btn btn-wa" href="{{ $waMsg('¡Hola! Quiero cotizar fotografía de graduación.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Cotizar</a>
</div></nav>

<header class="hero"><div class="wrap">
    <div>
        <div class="eyebrow">📸 Fotografía profesional de grados</div>
        <h1>Fotos de grado que se <span>recuerdan</span> toda la vida</h1>
        <p class="lead">Cubrimos tu graduación completa: retratos, ceremonia, fotos de grupo, anuarios, togas y birretes, diplomas y souvenirs. Para colegios y universidades en toda Colombia.</p>
        <div class="cta-row">
            <a class="btn btn-wa" href="{{ $waMsg('¡Hola! Quiero cotizar fotografía de graduación.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Pedir cotización</a>
            <a class="btn btn-ghost" href="#galeria"><i class="bi bi-images"></i> Ver galería</a>
        </div>
        <div class="trust">
            <div class="t"><b>+10</b> años de experiencia</div>
            <div class="t"><b>100%</b> colegios y universidades</div>
            <div class="t"><b>HD</b> entrega digital + impresa</div>
        </div>
    </div>
    <div class="cam-stage">
        @if($heroPics->count() > 0)<img class="float-pic fp1" src="{{ $heroPics->first()->url() }}" alt="muestra fotos de grado">@endif
        @if($heroPics->count() > 1)<img class="float-pic fp2" src="{{ $heroPics->get(1)->url() }}" alt="muestra fotografía de graduación">@endif
        <div>
            <div class="cam" id="cam">
                <div class="ring"></div>
                <div class="lens"></div>
                <div class="rec"></div>
            </div>
            <div class="shutter" style="text-align:center">
                <button class="btn btn-gold" id="shootBtn"><i class="bi bi-camera-fill"></i> ¡Tómame la foto!</button>
            </div>
        </div>
    </div>
</div></header>

<div class="marquee"><div class="track">
    @for($i=0;$i<2;$i++)
    <span>📷 Fotos de grado</span><span><b>·</b></span><span>🎓 Togas y birretes</span><span><b>·</b></span><span>📕 Anuarios</span><span><b>·</b></span><span>📜 Diplomas y actas</span><span><b>·</b></span><span>🎁 Souvenirs</span><span><b>·</b></span><span>🏫 Colegios y universidades</span><span><b>·</b></span><span>📸 Toda Colombia</span><span><b>·</b></span>
    @endfor
</div></div>

<section><div class="wrap">
    <div class="sec-head rv"><div class="eyebrow">Fácil y rápido</div><h2>¿Cómo funciona?</h2><p>En 3 pasos tu promoción tiene sus fotos listas.</p></div>
    <div class="steps">
        <div class="step"><div class="n">1</div><h4>Cotiza por WhatsApp</h4><p>Cuéntanos tu colegio, ciudad y número de estudiantes. Te armamos un paquete a la medida.</p></div>
        <div class="step"><div class="n">2</div><h4>Agendamos la sesión</h4><p>Coordinamos fecha y lugar: estudio, ceremonia o el sitio favorito del curso.</p></div>
        <div class="step"><div class="n">3</div><h4>Recibes tus recuerdos</h4><p>Galería digital en alta, impresiones, photobook y el anuario del colegio.</p></div>
    </div>
</div></section>

<section id="servicios"><div class="wrap">
    <div class="sec-head"><div class="eyebrow">Nuestros servicios</div><h2>Todo para tu graduación</h2><p>Desde la sesión en estudio hasta el anuario impreso, togas y souvenirs.</p></div>
    @foreach($categories as $cat)
        <div class="cat" id="cat-{{ $cat->slug }}">
            <div class="cat-h"><span class="cat-ico">{{ $cat->icon }}</span><h3>{{ $cat->name }}</h3>@if($cat->subtitle)<span class="s">{{ $cat->subtitle }}</span>@endif</div>
            @if($cat->services->count())
            <div class="grid">
                @foreach($cat->services as $s)
                    <div class="svc"><h4>{{ $s->title }}</h4>@if($s->subtitle)<div class="st">{{ $s->subtitle }}</div>@endif @if($s->description)<p>{{ $s->description }}</p>@endif</div>
                @endforeach
            </div>
            @endif
        </div>
    @endforeach
</div></section>

@if($gallery->count())
<div class="gal-wrap" id="galeria"><section><div class="wrap">
    <div class="sec-head"><div class="eyebrow">Portafolio</div><h2>Mira nuestro trabajo</h2><p>Fotos reales de grados, ceremonias, togas y anuarios. Filtra por lo que buscas.</p></div>
    <div class="filters">
        <button class="on" data-f="all">Ver todo</button>
        @foreach($galleryCats as $gc)<button data-f="c{{ $gc->id }}">{{ $gc->icon }} {{ $gc->name }}</button>@endforeach
    </div>
    <div class="masonry" id="masonry">
        @foreach($gallery as $img)
            <div class="ph" data-cat="{{ $img->photo_category_id ? 'c'.$img->photo_category_id : 'all' }}" data-src="{{ $img->url() }}">
                <img src="{{ $img->url() }}" alt="{{ $img->caption ?: ($img->category->name ?? 'Foto de grado') }}" loading="lazy">
                @if($img->caption)<div class="cap">{{ $img->caption }}</div>@endif
            </div>
        @endforeach
    </div>
</div></section></div>
@endif

@if($packages->count())
<section id="paquetes"><div class="wrap">
    <div class="sec-head"><div class="eyebrow">Paquetes de grado</div><h2>Elige el de tu promoción</h2><p>Combinamos fotografía y servicios según lo que necesita tu colegio. Cotiza sin compromiso.</p></div>
    <div class="pks">
        @foreach($packages as $pkg)
            <div class="pk {{ $pkg->featured ? 'feat' : '' }}">
                @if($pkg->featured)<span class="tag">★ Más elegido</span>@endif
                <h3>{{ $pkg->name }}</h3>@if($pkg->tagline)<div class="line">{{ $pkg->tagline }}</div>@endif
                <ul>@foreach(($pkg->features ?? []) as $f)<li>{{ $f }}</li>@endforeach</ul>
                <a class="btn btn-gold" href="{{ $waMsg('¡Hola! Me interesa el paquete '.$pkg->name.' de fotografía de grado.') }}" target="_blank" rel="noopener">Pedir cotización</a>
            </div>
        @endforeach
    </div>
</div></section>
@endif

<section class="faq"><div class="wrap" style="max-width:820px">
    <div class="sec-head"><div class="eyebrow">Dudas</div><h2>Preguntas frecuentes</h2></div>
    <details open><summary>¿Hacen fotos en el colegio o en estudio?</summary><p>Ambas. Sesión pre-ceremonia en estudio con iluminación profesional y cubrimiento de la ceremonia y fotos de grupo en tu colegio o el lugar que prefieras.</p></details>
    <details><summary>¿Cómo entregan las fotos?</summary><p>En galería digital en alta resolución y, según el paquete, con impresiones, photobook y el anuario del colegio.</p></details>
    <details><summary>¿Alquilan togas y birretes?</summary><p>Sí. Alquilamos togas y birretes según la talla de cada estudiante (negro, azul y vino tinto) y elaboramos estolas bordadas con el escudo de la institución.</p></details>
    <details><summary>¿Atienden colegios y universidades de otras ciudades?</summary><p>Cubrimos graduaciones en toda Colombia. Escríbenos por WhatsApp con tu ciudad y número de estudiantes para tu cotización.</p></details>
</div></section>

<section><div class="wrap">
    <div class="sec-head rv"><div class="eyebrow">Lo que dicen</div><h2>Promociones felices</h2></div>
    <div class="tst">
        <div class="tcard rv"><div class="stars">★★★★★</div><p>“Las fotos quedaron hermosas y la entrega fue súper rápida. Todo el curso quedó feliz con el anuario.”</p><div class="who">— Mariana G. · Estudiante de once</div></div>
        <div class="tcard rv"><div class="stars">★★★★★</div><p>“Nos cubrieron la ceremonia completa y las togas llegaron perfectas. Muy profesionales y atentos.”</p><div class="who">— Colegio San José · Coordinación</div></div>
        <div class="tcard rv"><div class="stars">★★★★★</div><p>“El retrato individual quedó increíble. Se nota la calidad en la edición. ¡100% recomendados!”</p><div class="who">— Andrés P. · Graduado</div></div>
    </div>
</div></section>

<div class="contact-wrap" id="contacto"><section><div class="wrap">
    <div class="contact">
        <div>
            <h2>Cuéntanos de tu graduación</h2>
            <p class="sub">Déjanos tus datos y te contactamos con la cotización. Sin compromiso.</p>
            @if(session('foto_ok'))<div class="okmsg">{{ session('foto_ok') }}</div>@endif
            @if($errors->any())<div class="okmsg" style="background:rgba(255,74,61,.12);border-color:rgba(255,74,61,.4);color:#ffb3ad">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('fotografia.lead') }}">
                @csrf
                <div class="field"><label>Nombre *</label><input name="name" value="{{ old('name') }}" required></div>
                <div class="field"><label>Teléfono / WhatsApp *</label><input name="phone" value="{{ old('phone') }}" required></div>
                <div class="field"><label>Ciudad</label><input name="city" value="{{ old('city') }}"></div>
                <div class="field"><label>Colegio / Universidad</label><input name="school" value="{{ old('school') }}"></div>
                <div class="field"><label>¿Qué necesitas?</label><textarea name="message" rows="3" placeholder="Ej: fotos de grado + togas para 40 estudiantes">{{ old('message') }}</textarea></div>
                <button class="btn btn-gold" style="width:100%;justify-content:center"><i class="bi bi-send"></i> Enviar y recibir cotización</button>
            </form>
        </div>
        <div class="wa-card">
            <div class="eyebrow">O escríbenos directo</div>
            <div class="big">¿Listos para las fotos de su promoción? 🎓</div>
            <a class="btn btn-wa" style="justify-content:center;font-size:17px;padding:16px" href="{{ $waMsg('¡Hola! Quiero cotizar la fotografía de graduación de mi promoción.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Escribir por WhatsApp</a>
            <a class="btn btn-ghost" style="justify-content:center" href="tel:+{{ $wa }}"><i class="bi bi-telephone"></i> +{{ $wa }}</a>
        </div>
    </div>
</div></section></div>

<footer><div class="wrap">© {{ date('Y') }} Glauren · Fotografía de Grados · Colombia · <a href="{{ url('/') }}" style="color:var(--gold)">Chaquetas de Prom</a></div></footer>

<a class="float-btn" href="{{ $waMsg('¡Hola! Quiero cotizar fotografía de graduación.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp" style="font-size:20px"></i> Cotizar</a>

<div class="lb" id="lb"><span class="x" id="lbx">&times;</span><img id="lbimg" src="" alt="foto"></div>

<script>
(function(){
    // Flash al cargar y al "disparar"
    var flash = document.getElementById('flash');
    function doFlash(){ flash.classList.remove('go'); void flash.offsetWidth; flash.classList.add('go'); }
    setTimeout(doFlash, 500);
    var shoot = document.getElementById('shootBtn');
    if(shoot) shoot.addEventListener('click', function(){ doFlash(); setTimeout(function(){ document.getElementById('contacto').scrollIntoView({behavior:'smooth'}); }, 260); });

    // Filtros de galería
    document.querySelectorAll('.filters button').forEach(function(b){
        b.addEventListener('click', function(){
            document.querySelectorAll('.filters button').forEach(x=>x.classList.remove('on'));
            b.classList.add('on');
            var f=b.getAttribute('data-f');
            document.querySelectorAll('#masonry .ph').forEach(function(ph){
                ph.style.display = (f==='all' || ph.getAttribute('data-cat')===f) ? '' : 'none';
            });
        });
    });

    // Lightbox
    var lb=document.getElementById('lb'), lbimg=document.getElementById('lbimg');
    document.querySelectorAll('#masonry .ph').forEach(function(ph){
        ph.addEventListener('click', function(){ lbimg.src=ph.getAttribute('data-src'); lb.classList.add('on'); });
    });
    function closeLb(){ lb.classList.remove('on'); }
    document.getElementById('lbx').addEventListener('click', closeLb);
    lb.addEventListener('click', function(e){ if(e.target===lb) closeLb(); });

    // Reveal al hacer scroll (aparecen con fade + subida, escalonado)
    var revealEls = document.querySelectorAll('.step,.svc,.pk,.tcard,.cat,.sec-head,.ph,.contact,.marquee');
    revealEls.forEach(function(el){ el.classList.add('rv'); });
    if('IntersectionObserver' in window){
        var io = new IntersectionObserver(function(entries){
            entries.forEach(function(e,i){
                if(e.isIntersecting){ var el=e.target; setTimeout(function(){ el.classList.add('in'); }, Math.min(i*60,240)); io.unobserve(el); }
            });
        }, { threshold:.12 });
        revealEls.forEach(function(el){ io.observe(el); });
    } else { revealEls.forEach(function(el){ el.classList.add('in'); }); }

    // Parallax de la cámara al mover el mouse
    var cam = document.getElementById('cam');
    if(cam && window.matchMedia('(pointer:fine)').matches){
        window.addEventListener('mousemove', function(ev){
            var rx = (ev.clientY/window.innerHeight - .5) * -10;
            var ry = (ev.clientX/window.innerWidth - .5) * 14;
            cam.style.transform = 'perspective(700px) rotateX('+rx+'deg) rotateY('+ry+'deg)';
        });
    }

    // Contador animado de las estadísticas del hero
    document.querySelectorAll('.trust .t b').forEach(function(b){
        var txt = b.textContent.trim();
        var m = txt.match(/^\+?(\d+)/);
        if(!m) return;
        var end = parseInt(m[1],10), pre = txt.startsWith('+')?'+':'', suf = txt.replace(/^\+?\d+/,'');
        var start=null;
        function step(ts){ if(!start) start=ts; var p=Math.min((ts-start)/900,1); b.textContent=pre+Math.floor(p*end)+suf; if(p<1) requestAnimationFrame(step); }
        requestAnimationFrame(step);
    });
})();
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    $wa = preg_replace('/\D/','', \App\Modules\Chaquetas\Models\Setting::get('contact_whatsapp','573021106592'));
    $allKeywords = collect($categories)->flatMap(fn($c)=>array_filter([$c->keywords]))->implode(', ');
@endphp
@include('partials.seo', [
    'seoKey' => 'fotografia',
    'seoTitleOverride' => 'Fotografía de Graduación en Colombia · Fotos de Grado | Glauren',
    'seoDescriptionOverride' => 'Fotografía profesional de graduación: fotos de grado, cubrimiento de ceremonia, anuarios, togas y birretes, diplomas y souvenirs. Paquetes para colegios y universidades.',
    'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>'Fotografía de grados','url'=>url('/fotografia-de-grados')]],
])
<meta name="keywords" content="fotografía de graduación, fotos de grado, fotos de graduación, anuario escolar, alquiler de togas y birretes, estolas de grado, diplomas, actas de grado, mosaico de grado, paquetes de grado{{ $allKeywords ? ', '.$allKeywords : '' }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    :root{ --ink:#12100f; --panel:#1b1917; --panel2:#232019; --line:rgba(255,255,255,.09); --bone:#f4f1ea; --muted:#a79f97; --gold:#c9a54b; --gold2:#e0cb74; }
    *{ box-sizing:border-box; margin:0; padding:0 }
    body{ background:var(--ink); color:var(--bone); font-family:'Hanken Grotesk',system-ui,sans-serif; line-height:1.6 }
    .wrap{ max-width:1120px; margin:0 auto; padding:0 20px }
    h1,h2,h3{ font-family:'Big Shoulders Display',sans-serif; line-height:1.04; text-transform:uppercase; letter-spacing:-.01em }
    a{ color:inherit; text-decoration:none }
    .top{ position:sticky; top:0; z-index:30; background:rgba(18,16,15,.85); backdrop-filter:blur(10px); border-bottom:1px solid var(--line) }
    .top .wrap{ display:flex; align-items:center; gap:16px; height:64px }
    .top .brand{ font-family:'Big Shoulders Display',sans-serif; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--gold) }
    .top .sp{ margin-left:auto }
    .btn{ display:inline-block; border-radius:999px; padding:12px 22px; font-weight:700; font-size:15px }
    .btn-wa{ background:#25d366; color:#04240f }
    .btn-gold{ background:var(--gold); color:#1a1405 }
    .btn-ghost{ border:1px solid var(--line); color:var(--bone) }
    /* hero */
    .hero{ padding:70px 0 54px; background:radial-gradient(900px 340px at 20% -10%,rgba(201,165,75,.22),transparent 60%),linear-gradient(120deg,#1c1a17,#201a12) }
    .hero h1{ font-size:clamp(38px,7vw,78px); font-weight:800 }
    .hero p{ color:var(--muted); max-width:620px; margin:16px 0 26px; font-size:18px }
    .eyebrow{ color:var(--gold); font-weight:700; letter-spacing:.14em; text-transform:uppercase; font-size:13px }
    .cta-row{ display:flex; gap:12px; flex-wrap:wrap }
    /* sections */
    section{ padding:54px 0 }
    .sec-head{ max-width:640px; margin-bottom:30px }
    .sec-head h2{ font-size:clamp(28px,4.5vw,44px); font-weight:800 }
    .sec-head p{ color:var(--muted); margin-top:10px }
    .cat{ margin-bottom:44px }
    .cat-title{ display:flex; align-items:baseline; gap:12px; border-bottom:1px solid var(--line); padding-bottom:12px; margin-bottom:22px; flex-wrap:wrap }
    .cat-title h3{ font-size:26px; font-weight:800 }
    .cat-title .sub{ color:var(--muted); font-size:15px }
    .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px }
    .svc{ background:var(--panel); border:1px solid var(--line); border-radius:16px; overflow:hidden; display:flex; flex-direction:column }
    .svc img{ width:100%; aspect-ratio:4/3; object-fit:cover; display:block }
    .svc .bd{ padding:16px 18px }
    .svc h4{ font-family:'Big Shoulders Display',sans-serif; font-size:19px; text-transform:uppercase; letter-spacing:.01em }
    .svc .st{ color:var(--gold); font-size:13px; font-weight:600; margin:2px 0 6px }
    .svc p{ color:var(--muted); font-size:14px }
    /* packages */
    .pk-wrap{ background:linear-gradient(180deg,#171511,#1d1811) }
    .pks{ display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px }
    .pk{ background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:26px; display:flex; flex-direction:column }
    .pk.feat{ border-color:var(--gold); box-shadow:0 0 0 1px var(--gold) inset }
    .pk .tag{ display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--gold); margin-bottom:8px }
    .pk h3{ font-size:26px; font-weight:800 }
    .pk .line{ color:var(--muted); font-size:14px; margin:4px 0 16px }
    .pk ul{ list-style:none; margin:0 0 20px; display:flex; flex-direction:column; gap:9px }
    .pk li{ font-size:14.5px; padding-left:26px; position:relative }
    .pk li::before{ content:'✔'; position:absolute; left:0; color:var(--gold) }
    .pk .btn{ margin-top:auto }
    /* faq */
    .faq details{ border:1px solid var(--line); border-radius:12px; padding:14px 18px; margin-bottom:10px; background:var(--panel) }
    .faq summary{ cursor:pointer; font-weight:700; font-size:16px }
    .faq p{ color:var(--muted); margin-top:10px; font-size:15px }
    footer{ border-top:1px solid var(--line); padding:40px 0; color:var(--muted); font-size:14px; text-align:center }
    .cta-band{ background:radial-gradient(700px 240px at 50% 0,rgba(201,165,75,.18),transparent 60%); text-align:center; padding:60px 0 }
    .cta-band h2{ font-size:clamp(28px,4.5vw,46px); font-weight:800 }
    .cta-band p{ color:var(--muted); margin:12px 0 24px }
</style>
</head>
<body>

<div class="top"><div class="wrap">
    <a href="{{ url('/') }}" class="brand">Glauren</a>
    <span style="color:var(--muted);font-size:14px">Fotografía de Grados</span>
    <a class="sp btn btn-wa" href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola! Quiero cotizar fotografía de graduación para mi curso/colegio.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Cotizar</a>
</div></div>

<header class="hero"><div class="wrap">
    <div class="eyebrow">Fotografía profesional de graduación</div>
    <h1>Fotos de grado que se recuerdan toda la vida</h1>
    <p>Cubrimos tu graduación de principio a fin: retratos, ceremonia, fotos de grupo, anuarios, togas y birretes, diplomas y souvenirs. Paquetes para colegios y universidades en toda Colombia.</p>
    <div class="cta-row">
        <a class="btn btn-wa" href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola! Quiero cotizar fotografía de graduación.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Pedir cotización</a>
        <a class="btn btn-ghost" href="#paquetes">Ver paquetes</a>
    </div>
</div></header>

<section><div class="wrap">
    <div class="sec-head">
        <div class="eyebrow">Nuestros servicios</div>
        <h2>Todo para tu graduación, en un solo lugar</h2>
        <p>Desde la sesión en estudio hasta el anuario impreso. Elige lo que tu promoción necesita.</p>
    </div>

    @foreach($categories as $cat)
        <div class="cat" id="{{ $cat->slug }}">
            <div class="cat-title">
                <h3>{{ $cat->icon }} {{ $cat->name }}</h3>
                @if($cat->subtitle)<span class="sub">{{ $cat->subtitle }}</span>@endif
            </div>
            @if($cat->services->count())
            <div class="grid">
                @foreach($cat->services as $s)
                    <div class="svc">
                        @if($s->imageUrl())<img src="{{ $s->imageUrl() }}" alt="{{ $s->title }}" loading="lazy">@endif
                        <div class="bd">
                            <h4>{{ $s->title }}</h4>
                            @if($s->subtitle)<div class="st">{{ $s->subtitle }}</div>@endif
                            @if($s->description)<p>{{ $s->description }}</p>@endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    @endforeach
</div></section>

@if($packages->count())
<div class="pk-wrap" id="paquetes"><section><div class="wrap">
    <div class="sec-head">
        <div class="eyebrow">Paquetes de grado</div>
        <h2>Elige el paquete de tu promoción</h2>
        <p>Combinamos fotografía y servicios según lo que tu colegio necesita. Pide la cotización sin compromiso.</p>
    </div>
    <div class="pks">
        @foreach($packages as $pkg)
            <div class="pk {{ $pkg->featured ? 'feat' : '' }}">
                @if($pkg->featured)<span class="tag">Más elegido</span>@endif
                <h3>{{ $pkg->name }}</h3>
                @if($pkg->tagline)<div class="line">{{ $pkg->tagline }}</div>@endif
                <ul>@foreach(($pkg->features ?? []) as $f)<li>{{ $f }}</li>@endforeach</ul>
                <a class="btn btn-gold" href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola! Me interesa el paquete '.$pkg->name.' de fotografía de grado.') }}" target="_blank" rel="noopener">Pedir cotización</a>
            </div>
        @endforeach
    </div>
</div></section></div>
@endif

<section class="faq"><div class="wrap">
    <div class="sec-head"><div class="eyebrow">Preguntas frecuentes</div><h2>Resolvemos tus dudas</h2></div>
    <details><summary>¿Hacen fotos en el colegio o en estudio?</summary><p>Ambas. Hacemos sesión pre-ceremonia en estudio con iluminación profesional y también cubrimos la ceremonia y las fotos de grupo en tu colegio o el lugar que prefieras.</p></details>
    <details><summary>¿Cómo entregan las fotos?</summary><p>En galería digital en alta resolución y, según el paquete, con impresiones, photobook y anuario del colegio.</p></details>
    <details><summary>¿Alquilan togas y birretes?</summary><p>Sí. Alquilamos togas y birretes según la talla de cada estudiante (negro, azul y vino tinto) y elaboramos estolas bordadas con el escudo de la institución.</p></details>
    <details><summary>¿Atienden colegios y universidades de otras ciudades?</summary><p>Sí, cubrimos graduaciones en toda Colombia. Escríbenos por WhatsApp con tu ciudad y la cantidad de estudiantes para armar tu cotización.</p></details>
</div></section>

<div class="cta-band"><div class="wrap">
    <h2>¿Listos para las fotos de su promoción?</h2>
    <p>Cuéntanos tu colegio, ciudad y número de estudiantes. Te armamos un paquete a la medida.</p>
    <a class="btn btn-wa" href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola! Quiero cotizar la fotografía de graduación de mi promoción.') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Escríbenos por WhatsApp</a>
</div></div>

<footer><div class="wrap">
    © {{ date('Y') }} Glauren · Fotografía de Grados · Colombia ·
    <a href="{{ url('/') }}" style="color:var(--gold)">Chaquetas de Prom</a>
</div></footer>

</body>
</html>

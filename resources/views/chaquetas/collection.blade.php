<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $wa = preg_replace('/\D/','', \App\Modules\Chaquetas\Models\Setting::get('contact_whatsapp','573246750207')); @endphp
    @include('partials.seo', [
        'seoKey' => 'collection',
        'seoTitleOverride' => $collection->title.' · Chaquetas de Prom | Glauren',
        'seoDescriptionOverride' => $collection->description ? \Illuminate\Support\Str::limit(strip_tags($collection->description),155) : 'Descubre nuestro estilo '.$collection->title.' de chaquetas de promoción. Diseña la tuya en 3D.',
        'ogImageOverride' => $collection->imageUrl(),
        'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>$collection->title,'url'=>$collection->targetUrl()]],
    ])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@500;600;700;800&family=Hanken+Grotesk:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@1,6..72,400;1,6..72,500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root{ --ink:#10151f; --ink2:#161c28; --bone:#f4f1ea; --muted:#9aa2b1; --line:rgba(255,255,255,.10); --gold:#c9a54b; --gold2:#e0cb74; }
        *{ box-sizing:border-box; }
        body{ margin:0; background:var(--ink); color:var(--bone); font-family:'Hanken Grotesk',system-ui,sans-serif; }
        a{ color:inherit; text-decoration:none; }
        .wrap{ max-width:1180px; margin:0 auto; padding:0 24px; }
        /* Nav */
        nav.top{ position:sticky; top:0; z-index:40; display:flex; align-items:center; justify-content:space-between;
            padding:15px 24px; background:rgba(16,21,31,.9); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); }
        .brand{ font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; letter-spacing:.05em; font-size:22px; display:flex; align-items:center; gap:10px; }
        .brand .mk{ width:28px;height:28px;border:2px solid var(--gold);border-radius:50%;display:grid;place-items:center;color:var(--gold);font-size:12px; }
        .cta{ background:linear-gradient(135deg,var(--gold),var(--gold2)); color:#1a1405; font-weight:700; padding:10px 18px; border-radius:10px; font-size:14px; }
        .cta:hover{ filter:brightness(1.06); }
        /* Header */
        header.head{ padding:54px 0 34px; border-bottom:1px solid var(--line);
            background:radial-gradient(900px 380px at 85% -20%, rgba(201,165,75,.14), transparent 60%); }
        .crumb{ font-size:13px; color:var(--muted); margin-bottom:16px; }
        .crumb a:hover{ color:var(--gold); }
        .ey{ font-family:'Hanken Grotesk',sans-serif; text-transform:uppercase; letter-spacing:.22em; font-size:11px; color:var(--gold); font-weight:600; }
        h1{ font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; letter-spacing:.01em; font-size:clamp(38px,7vw,74px); line-height:.98; margin:10px 0 0; }
        .lead{ max-width:640px; color:#cdd2db; font-size:17px; line-height:1.6; margin:16px 0 0; }
        .lead em{ font-family:'Newsreader',serif; font-style:italic; color:var(--gold2); }
        /* Gallery */
        .gallery{ columns:3; column-gap:16px; padding:40px 0 20px; }
        .gallery figure{ break-inside:avoid; margin:0 0 16px; border-radius:14px; overflow:hidden; border:1px solid var(--line); background:var(--ink2); cursor:zoom-in; position:relative; }
        .gallery img{ width:100%; display:block; transition:transform .5s ease; }
        .gallery figure:hover img{ transform:scale(1.04); }
        .gallery figcaption{ position:absolute; left:0; right:0; bottom:0; padding:14px 14px 12px; font-size:13px;
            background:linear-gradient(transparent,rgba(9,12,18,.85)); opacity:0; transition:opacity .3s; }
        .gallery figure:hover figcaption{ opacity:1; }
        @media(max-width:900px){ .gallery{ columns:2; } }
        @media(max-width:560px){ .gallery{ columns:1; } }
        .empty{ padding:60px 24px; text-align:center; color:var(--muted); border:1px dashed var(--line); border-radius:14px; margin:40px 0; }
        /* CTA band */
        .band{ margin:30px 0 0; padding:48px 24px; text-align:center; border-top:1px solid var(--line);
            background:radial-gradient(700px 300px at 50% 120%, rgba(201,165,75,.16), transparent 60%); }
        .band h2{ font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; font-size:clamp(28px,5vw,46px); margin:0 0 10px; }
        .band p{ color:#cdd2db; margin:0 0 22px; }
        .btns{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
        .btn-line{ border:1px solid var(--line); padding:11px 20px; border-radius:10px; font-weight:600; }
        .btn-line:hover{ border-color:var(--gold); color:var(--gold); }
        .btn-wa{ background:#25d366; color:#062b13; font-weight:700; padding:11px 20px; border-radius:10px; }
        /* Otras colecciones */
        .others{ padding:44px 0 70px; }
        .others h3{ font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; font-size:20px; letter-spacing:.04em; margin:0 0 20px; }
        .ocols{ display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
        .ocard{ position:relative; aspect-ratio:4/5; border-radius:12px; overflow:hidden; border:1px solid var(--line); }
        .ocard img{ width:100%; height:100%; object-fit:cover; transition:transform .5s; }
        .ocard:hover img{ transform:scale(1.06); }
        .ocard span{ position:absolute; left:12px; bottom:11px; font-family:'Big Shoulders Display',sans-serif; text-transform:uppercase; font-size:15px; z-index:2; }
        .ocard .og{ position:absolute; inset:0; background:linear-gradient(transparent 40%,rgba(9,12,18,.85)); }
        @media(max-width:820px){ .ocols{ grid-template-columns:repeat(2,1fr); } }
        /* Lightbox */
        #lb{ position:fixed; inset:0; background:rgba(6,8,13,.95); display:none; align-items:center; justify-content:center; z-index:100; padding:24px; }
        #lb.open{ display:flex; }
        #lb img{ max-width:92vw; max-height:88vh; border-radius:10px; }
        #lb .x{ position:absolute; top:20px; right:26px; font-size:30px; color:#fff; cursor:pointer; opacity:.8; }
        #lb .x:hover{ opacity:1; }
    </style>
</head>
<body>
<nav class="top">
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span> Glauren</a>
    <a href="{{ route('personalizar') }}" class="cta">Diseña tu chaqueta en 3D</a>
</nav>

<header class="head">
    <div class="wrap">
        <div class="crumb"><a href="{{ route('home') }}">Inicio</a> <span style="opacity:.5">/</span> Colecciones <span style="opacity:.5">/</span> {{ $collection->title }}</div>
        <span class="ey">Colección</span>
        <h1>{{ $collection->title }}</h1>
        @if($collection->description)
            <p class="lead">{!! nl2br(e($collection->description)) !!}</p>
        @else
            <p class="lead">Explora fotos reales de nuestras chaquetas <em>{{ $collection->title }}</em>. Cada una se personaliza con los colores, escudos y nombres de tu promoción.</p>
        @endif
    </div>
</header>

<div class="wrap">
    @if($collection->images->count())
        <div class="gallery">
            @foreach($collection->images as $img)
                <figure onclick="openLb('{{ $img->url() }}', @js($img->caption ?: 'Chaqueta de prom '.$collection->title.' · Glauren'))">
                    <img src="{{ $img->url() }}" alt="{{ $img->caption ?: 'Chaqueta de prom '.$collection->title.' · Glauren' }}" loading="lazy">
                    @if($img->caption)<figcaption>{{ $img->caption }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
    @elseif($collection->imageUrl())
        <div class="gallery">
            <figure onclick="openLb('{{ $collection->imageUrl() }}')">
                <img src="{{ $collection->imageUrl() }}" alt="Chaqueta de prom {{ $collection->title }} · Glauren" loading="lazy">
            </figure>
        </div>
    @else
        <div class="empty"><i class="bi bi-images" style="font-size:30px;display:block;margin-bottom:10px;opacity:.6"></i> Pronto subiremos más fotos de este estilo.</div>
    @endif
</div>

<section class="band">
    <div class="wrap">
        <h2>¿Te gusta este estilo?</h2>
        <p>Diséñala a tu gusto en 3D o escríbenos y te asesoramos con tu promoción.</p>
        <div class="btns">
            <a href="{{ route('personalizar') }}" class="cta">Diseñar en 3D</a>
            <a href="https://wa.me/{{ $wa }}" target="_blank" class="btn-wa"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            <a href="{{ route('home') }}" class="btn-line">Volver al inicio</a>
        </div>
    </div>
</section>

@if($others->count())
<section class="others">
    <div class="wrap">
        <h3>Otros estilos</h3>
        <div class="ocols">
            @foreach($others as $o)
                <a href="{{ $o->targetUrl() }}" class="ocard">
                    <img src="{{ $o->imageUrl() }}" alt="Chaquetas de prom estilo {{ $o->title }} · Glauren" loading="lazy">
                    <div class="og"></div>
                    <span>{{ $o->title }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<div id="lb" onclick="closeLb(event)">
    <span class="x" onclick="closeLb(event)">&times;</span>
    <img id="lbImg" src="" alt="">
</div>
<script>
    function openLb(src, alt){ var i=document.getElementById('lbImg'); i.src = src; i.alt = alt || 'Chaqueta de prom · Glauren'; document.getElementById('lb').classList.add('open'); }
    function closeLb(e){ document.getElementById('lb').classList.remove('open'); }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeLb(); });
</script>
</body>
</html>

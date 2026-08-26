<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'page',
    'seoModel' => $page,
    'breadcrumbs' => [
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => $page->title, 'url' => route('page.show', $page->slug)],
    ],
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
<style>
    body{background:#0d0d0f;color:#f4efe3;font-family:var(--g-font-body);margin:0}
    .article{max-width:840px;margin:0 auto;padding:44px 24px 80px}
    .article h1{font-family:var(--g-font-display);text-transform:uppercase;font-weight:800;font-size:clamp(34px,6vw,60px);line-height:.92;margin:0 0 24px}
    .content{font-size:17px;line-height:1.8}
    .content h2,.content h3{font-family:var(--g-font-display);font-weight:700;text-transform:uppercase;letter-spacing:.02em;color:var(--g-gold);margin:34px 0 12px}
    .content img{max-width:100%;border-radius:12px}
    .content a{color:var(--g-gold)}
    .back{display:inline-block;margin-top:36px;color:var(--g-muted);text-decoration:none}
</style>
</head>
<body>
    @include('chaquetas.partials.topbar')
    <article class="article">
        <h1>{{ $page->title }}</h1>
        @if(trim((string) $page->body) !== '')
            <div class="content">{!! \App\Modules\Chaquetas\Support\Seo::imageAlts($page->body, $page->focus_keyword ?: $page->title) !!}</div>
        @endif
    </article>

    {{-- Módulos de esta página (Admin → Páginas → Módulos) --}}
    @include('chaquetas.partials.home-blocks', ['pageId' => $page->id])

    <div style="max-width:840px;margin:0 auto;padding:8px 24px 70px">
        <a href="{{ route('home') }}" class="back">← Volver al inicio</a>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'noticias',
    'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>'Noticias','url'=>route('news.index')]],
])
<link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
<style>
    body{background:var(--g-ink);color:var(--g-bone);font-family:var(--g-font-body);margin:0}
    .wrap{max-width:1100px;margin:0 auto;padding:48px 24px 80px}
    h1{font-family:var(--g-font-display);text-transform:uppercase;font-size:clamp(34px,5vw,60px);line-height:1;margin:0 0 8px}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin-top:34px}
    .post{background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);overflow:hidden;text-decoration:none;color:var(--g-bone);transition:.16s;display:block}
    .post:hover{transform:translateY(-4px);border-color:var(--g-gold)}
    .post .cover{aspect-ratio:16/10;background:var(--g-surface-2) center/cover}
    .post .body{padding:16px}
    .post h3{font-size:18px;margin:0 0 6px}
    .post .date{font-family:var(--g-font-label);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--g-gold)}
    .post p{color:var(--g-muted);font-size:14px;margin:8px 0 0}
    .empty{color:var(--g-muted);margin-top:30px}
    @media(max-width:820px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
    @include('chaquetas.partials.topbar')
    <div class="wrap">
        <span class="g-eyebrow">Blog</span>
        <h1>Noticias</h1>
        @if($posts->count())
            <div class="grid">
                @foreach($posts as $p)
                    <a href="{{ route('news.show', $p->slug) }}" class="post">
                        <div class="cover" @if($p->coverUrl()) style="background-image:url('{{ $p->coverUrl() }}')" @endif></div>
                        <div class="body">
                            <div class="date">{{ optional($p->published_at)->format('d M Y') }}</div>
                            <h3>{{ $p->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($p->excerpt, 90) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div style="margin-top:30px">{{ $posts->links() }}</div>
        @else
            <p class="empty">Aún no hay noticias publicadas.</p>
        @endif
    </div>
</body>
</html>

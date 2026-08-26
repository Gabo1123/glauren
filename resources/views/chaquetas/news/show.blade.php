<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'post',
    'seoModel' => $post,
    'article' => $post,
    'breadcrumbs' => [
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Noticias', 'url' => route('news.index')],
        ['name' => $post->title, 'url' => route('news.show', $post->slug)],
    ],
])
<link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
<style>
    body{background:var(--g-ink);color:var(--g-bone);font-family:var(--g-font-body);margin:0}
    .article{max-width:800px;margin:0 auto;padding:40px 24px 80px}
    .date{font-family:var(--g-font-label);font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--g-gold)}
    h1{font-family:var(--g-font-display);text-transform:uppercase;font-size:clamp(30px,4.5vw,52px);line-height:1.02;margin:10px 0 20px}
    .cover{width:100%;border-radius:14px;margin-bottom:26px}
    .content{font-size:17px;line-height:1.8}
    .content img{max-width:100%;border-radius:12px}
    .content a{color:var(--g-gold)}
    .content h2,.content h3{scroll-margin-top:80px;font-family:var(--g-font-display);text-transform:uppercase;margin:34px 0 12px}
    .back{display:inline-block;margin-top:36px;color:var(--g-muted);text-decoration:none}
    /* Autor (byline) */
    .byline{display:flex;align-items:center;gap:12px;margin:0 0 26px;padding:14px 0;border-top:1px solid var(--g-line);border-bottom:1px solid var(--g-line)}
    .byline .av{width:44px;height:44px;border-radius:50%;background:var(--g-gold);color:#1a1405;display:grid;place-items:center;font-family:var(--g-font-display);font-weight:900;font-size:20px;flex:none}
    .byline .who b{display:block;font-size:15px}
    .byline .who span{color:var(--g-muted);font-size:13px}
    /* Tabla de contenido */
    .toc{background:var(--g-surface);border:1px solid var(--g-line);border-left:3px solid var(--g-gold);border-radius:10px;padding:18px 22px;margin:0 0 30px}
    .toc-t{font-family:var(--g-font-label);text-transform:uppercase;letter-spacing:.1em;font-size:11px;color:var(--g-gold);margin-bottom:10px}
    .toc ol{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:7px}
    .toc a{color:var(--g-bone);text-decoration:none;font-size:14.5px}
    .toc a:hover{color:var(--g-gold)}
    .toc .lvl3{margin-left:16px;font-size:13.5px;list-style:circle}
</style>
</head>
<body>
    @include('chaquetas.partials.topbar')
    @php
        $processed = \App\Modules\Chaquetas\Support\Seo::tableOfContents($post->body);
        $bodyHtml = \App\Modules\Chaquetas\Support\Seo::imageAlts($processed['html'], $post->focus_keyword ?: $post->title);
        $toc = $processed['toc'];
    @endphp
    <article class="article">
        <div class="date">{{ optional($post->published_at)->format('d M Y') }} · {{ $post->readingMinutes() }} min de lectura</div>
        <h1>{{ $post->title }}</h1>

        {{-- Autor --}}
        <div class="byline">
            <div class="av">{{ mb_strtoupper(mb_substr($post->authorName(),0,1)) }}</div>
            <div class="who">
                <b>{{ $post->authorName() }}</b>
                <span>{{ $post->authorRole() }}</span>
            </div>
        </div>

        @if($post->coverUrl())<img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" class="cover">@endif
        @if($post->excerpt)<p class="g-muted" style="font-size:19px">{{ $post->excerpt }}</p>@endif

        {{-- Tabla de contenido --}}
        @if(count($toc) >= 3)
        <nav class="toc" aria-label="Tabla de contenido">
            <div class="toc-t">En este artículo</div>
            <ol>
                @foreach($toc as $t)
                    <li class="{{ $t['level'] === 3 ? 'lvl3' : '' }}"><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>
                @endforeach
            </ol>
        </nav>
        @endif

        <div class="content">{!! $bodyHtml !!}</div>
        <a href="{{ route('news.index') }}" class="back">← Todas las noticias</a>
    </article>
</body>
</html>

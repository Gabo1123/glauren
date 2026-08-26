@php
    use App\Modules\Chaquetas\Models\HomeBlock;
    if (isset($pageId)) {
        $blks = HomeBlock::active()->forPage($pageId)->get();
    } else {
        $blks = HomeBlock::active()->home()->where('position', $position)->get();
    }
@endphp
@if($blks->count())
@once
<style>
    .hblk{ padding:56px 0; }
    .hblk.sp-compact{ padding:28px 0; }
    .hblk.sp-large{ padding:92px 0; }
    /* Imagen de fondo con capa para legibilidad */
    .hblk.has-bgimg{ position:relative; background-size:cover!important; background-position:center!important; color:#fff; }
    .hblk.has-bgimg::before{ content:'';position:absolute;inset:0;background:rgba(8,8,10,.55);z-index:0; }
    .hblk.has-bgimg > .wrap{ position:relative;z-index:1; }
    .hblk .wrap{ max-width:1120px; margin:0 auto; padding:0 22px; }
    .hblk.w-wide .wrap{ max-width:1320px; }
    .hblk.w-full .wrap{ max-width:100%; padding:0 40px; }
    .hblk.t-dark{ background:var(--ink,#0d0d0f); color:var(--paper,#f4efe3); }
    .hblk.t-light{ background:#f4efe3; color:#141418; }
    .hblk.t-gold{ background:linear-gradient(120deg,#e9b83f,#f2cf63); color:#1a1405; }
    .hblk.a-center{ text-align:center; }
    .hblk.a-left{ text-align:left; }
    .hblk h2{ font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(28px,4.5vw,46px);line-height:.95;margin:0 0 12px; }
    .hblk .sub{ font-size:18px;opacity:.85;max-width:680px;margin:0 0 18px; }
    .hblk.a-center .sub{ margin-left:auto;margin-right:auto; }
    .hblk .rich{ font-size:16px;line-height:1.8; }
    .hblk .rich :is(h2,h3){ font-size:22px;margin:18px 0 8px; }
    .hblk .rich img{ max-width:100%;border-radius:12px; }
    .hblk .rich a{ color:inherit;text-decoration:underline; }
    .hblk .bbtn{ display:inline-block;margin-top:16px;background:#111;color:#fff;border:2px solid #000;border-radius:999px;padding:13px 26px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;text-decoration:none;box-shadow:4px 4px 0 rgba(0,0,0,.35);transition:transform .12s,box-shadow .12s; }
    .hblk.t-dark .bbtn{ background:var(--gold,#e9b83f);color:#1a1405;box-shadow:4px 4px 0 #000; }
    .hblk.t-light .bbtn{ background:#111;color:#fff; }
    .hblk .bbtn:hover{ transform:translate(2px,2px);box-shadow:2px 2px 0 rgba(0,0,0,.35); }
    .hblk .imgtxt{ display:grid;grid-template-columns:1fr 1fr;gap:34px;align-items:center;text-align:left; }
    .hblk .imgtxt img{ width:100%;border-radius:16px;border:3px solid #000;box-shadow:8px 8px 0 rgba(0,0,0,.3);display:block; }
    .hblk .imgtxt.img-right > img{ order:2; }
    .hblk .imgtxt.img-right > div{ order:1; }
    .hblk .vframe{ position:relative;max-width:900px;margin:8px auto 0;border:3px solid #000;border-radius:14px;overflow:hidden;box-shadow:8px 8px 0 rgba(0,0,0,.35);background:#000;aspect-ratio:16/9; }
    .hblk .vframe.tk{ max-width:340px;aspect-ratio:9/16; }
    .hblk .vframe iframe{ position:absolute;inset:0;width:100%;height:100%;border:0; }
    /* Módulos: beneficios / pasos / números */
    .hblk .mgrid{ display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:24px;text-align:left; }
    .hblk .mgrid.c3{ grid-template-columns:repeat(3,1fr); }
    .hblk .mcard{ border:2px solid #000;background:var(--panel,#1a1a20);border-radius:12px;padding:22px 20px;box-shadow:6px 6px 0 #000;position:relative; }
    .hblk.t-light .mcard,.hblk.t-gold .mcard{ background:#fff;color:#141418; }
    .hblk .mcard .em{ width:50px;height:50px;border-radius:13px;background:rgba(233,184,63,.15);border:1.5px solid var(--gold,#e9b83f);display:grid;place-items:center;font-size:24px;margin-bottom:12px; }
    .hblk .mcard h4{ font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;margin:0 0 4px; }
    .hblk .mcard p{ opacity:.7;font-size:14px;margin:0;line-height:1.45; }
    .hblk .mcard .no{ width:44px;height:44px;border-radius:50%;background:var(--gold,#e9b83f);color:#1c1503;border:2px solid #000;box-shadow:3px 3px 0 #000;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:20px;margin-bottom:12px; }
    .hblk .stats{ display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:24px;text-align:center; }
    .hblk .stats .sv{ font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:clamp(38px,6vw,64px);color:var(--gold,#e9b83f);line-height:1; }
    .hblk .stats .sl{ opacity:.7;font-size:13px;text-transform:uppercase;letter-spacing:.06em;margin-top:6px; }
    .hblk .galgrid{ gap:12px; }
    .hblk .galgrid img{ width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px;border:2px solid #000;box-shadow:5px 5px 0 rgba(0,0,0,.3);transition:transform .15s; }
    .hblk .galgrid a:hover img{ transform:translate(-2px,-2px); }
    /* Columnas elegibles (1–6) */
    .hblk .mgrid.cols-1,.hblk .stats.cols-1{ grid-template-columns:1fr; }
    .hblk .mgrid.cols-2,.hblk .stats.cols-2{ grid-template-columns:repeat(2,1fr); }
    .hblk .mgrid.cols-3,.hblk .stats.cols-3{ grid-template-columns:repeat(3,1fr); }
    .hblk .mgrid.cols-4,.hblk .stats.cols-4{ grid-template-columns:repeat(4,1fr); }
    .hblk .mgrid.cols-5,.hblk .stats.cols-5{ grid-template-columns:repeat(5,1fr); }
    .hblk .mgrid.cols-6,.hblk .stats.cols-6{ grid-template-columns:repeat(6,1fr); }
    @media(max-width:820px){ .hblk .mgrid,.hblk .mgrid.c3,.hblk [class*="cols-"]{ grid-template-columns:1fr 1fr!important; } .hblk .stats{ grid-template-columns:1fr 1fr;gap:26px; } }
    @media(max-width:520px){ .hblk .mgrid,.hblk .mgrid.c3,.hblk [class*="cols-"]{ grid-template-columns:1fr!important; } }
    @media(max-width:760px){ .hblk .imgtxt{ grid-template-columns:1fr; } }
</style>
@endonce

@foreach($blks as $b)
@php $bgImg = $b->bgImageUrl(); @endphp
<section class="hblk t-{{ $b->theme }} a-{{ $b->align }} sp-{{ $b->spacing ?: 'normal' }} w-{{ $b->width ?: 'normal' }} {{ $bgImg ? 'has-bgimg' : '' }}"
    @if($bgImg) style="background-image:url('{{ $bgImg }}')" @elseif($b->bg_color) style="background:{{ $b->bg_color }}" @endif>
    <div class="wrap">
        @switch($b->type)
            @case('texto')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @if($b->body)<div class="rich">{!! \App\Modules\Chaquetas\Support\Seo::imageAlts($b->body, $b->title ?: 'Glauren') !!}</div>@endif
                @break

            @case('imagen_texto')
                <div class="imgtxt {{ ($b->img_pos ?? 'left')==='right' ? 'img-right' : '' }}">
                    @if($b->imageUrl())<img src="{{ $b->imageUrl() }}" alt="{{ $b->title ?: 'Glauren' }}" loading="lazy" decoding="async">@endif
                    <div>
                        @if($b->title)<h2>{{ $b->title }}</h2>@endif
                        @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                        @if($b->body)<div class="rich">{!! \App\Modules\Chaquetas\Support\Seo::imageAlts($b->body, $b->title ?: 'Glauren') !!}</div>@endif
                        @if($b->button_text && $b->button_url)<a class="bbtn" href="{{ $b->button_url }}">{{ $b->button_text }}</a>@endif
                    </div>
                </div>
                @break

            @case('cta')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @if($b->button_text && $b->button_url)<a class="bbtn" href="{{ $b->button_url }}">{{ $b->button_text }}</a>@endif
                @break

            @case('video')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @php $emb = $b->videoEmbed(); @endphp
                @if($emb)
                    <div class="vframe {{ $emb['type']==='tk' ? 'tk' : '' }}">
                        <iframe src="{{ $emb['url'] }}" title="{{ $b->title ?: 'Video Glauren' }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                @endif
                @break

            @case('features')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @php $items = $b->data ?: []; $colc = $b->columns ? 'cols-'.$b->columns : (count($items)===3 ? 'c3' : ''); @endphp
                <div class="mgrid {{ $colc }}">
                    @foreach($items as $it)
                        <div class="mcard">
                            @if(!empty($it['emoji']))<div class="em">{{ $it['emoji'] }}</div>@endif
                            <h4>{{ $it['title'] ?? '' }}</h4>
                            <p>{{ $it['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
                @break

            @case('steps')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @php $items = $b->data ?: []; $colc = $b->columns ? 'cols-'.$b->columns : (count($items)===3 ? 'c3' : ''); @endphp
                <div class="mgrid {{ $colc }}">
                    @foreach($items as $i => $it)
                        <div class="mcard">
                            <div class="no">{{ $i+1 }}</div>
                            <h4>{{ $it['title'] ?? '' }}</h4>
                            <p>{{ $it['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
                @break

            @case('stats')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @php $items = $b->data ?: []; $colc = $b->columns ? 'cols-'.$b->columns : ''; @endphp
                <div class="stats {{ $colc }}">
                    @foreach($items as $it)
                        <div><div class="sv">{{ $it['value'] ?? '' }}</div><div class="sl">{{ $it['label'] ?? '' }}</div></div>
                    @endforeach
                </div>
                @break

            @case('galeria')
                @if($b->title)<h2>{{ $b->title }}</h2>@endif
                @if($b->subtitle)<div class="sub">{{ $b->subtitle }}</div>@endif
                @php $imgs = $b->data ?: []; $colc = $b->columns ? 'cols-'.$b->columns : 'cols-3'; @endphp
                <div class="mgrid galgrid {{ $colc }}">
                    @foreach($imgs as $img)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" target="_blank" rel="noopener">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" alt="{{ $b->title ?: 'Foto' }}" loading="lazy" decoding="async">
                        </a>
                    @endforeach
                </div>
                @break

            @case('html')
                <div class="rich">{!! $b->body !!}</div>
                @break
        @endswitch
    </div>
</section>
@endforeach
@endif

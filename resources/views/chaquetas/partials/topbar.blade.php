@php
    use App\Modules\Chaquetas\Models\MenuItem;
    use App\Modules\Chaquetas\Models\Setting;
    $navItems = MenuItem::tree();
    $brand = Setting::get('brand_name', 'Glauren');
    $brandLogo = Setting::get('brand_logo');
@endphp
<style>
    .tbnav{position:sticky;top:0;z-index:60;background:rgba(13,13,15,.94);backdrop-filter:blur(10px);border-bottom:2px solid #000}
    .tbnav-in{max-width:1200px;margin:0 auto;padding:0 24px;height:70px;display:flex;align-items:center;justify-content:space-between}
    .tb-brand{display:flex;align-items:center;gap:11px;text-decoration:none}
    .tb-brand .mk{width:42px;height:42px;border-radius:10px;background:#e9b83f;color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:23px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .tb-brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:24px;letter-spacing:.04em;color:#f4efe3}
    .tb-links{display:flex;gap:4px;align-items:center}
    .tb-links a{color:#f4efe3;text-decoration:none;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:16px;padding:8px 14px;border-radius:8px}
    .tb-links a:hover{color:#e9b83f;background:rgba(233,184,63,.08)}
    .tb-cta{margin-left:6px;background:#e9b83f;color:#1c1503 !important;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .tb-cta:hover{transform:translate(2px,2px);box-shadow:1px 1px 0 #000;background:#e9b83f !important;color:#1c1503 !important}
    .tb-sep{width:1px;height:26px;background:rgba(244,239,227,.18);margin:0 8px;flex:none}
    .tb-contact{display:inline-flex;align-items:center;gap:6px;border:2px solid #e9b83f;color:#e9b83f !important;border-radius:8px}
    .tb-contact .ic{font-size:.9em}
    .tb-contact:hover{background:#e9b83f !important;color:#1c1503 !important}
    .tb-contact.active{background:rgba(233,184,63,.14)}
    .tb-dd{position:relative}
    .tb-dd > .lb{cursor:pointer;display:inline-flex;align-items:center;gap:5px;color:#f4efe3;padding:8px 14px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:16px}
    .tb-dd:hover > .lb{color:#e9b83f}
    .tb-dd .mn{position:absolute;top:100%;left:0;background:#141418;border:2px solid #000;border-radius:8px;min-width:250px;padding:6px;display:none;flex-direction:column;box-shadow:6px 6px 0 #000;z-index:65}
    .tb-dd:hover .mn{display:flex}
    .tb-dd .mn a{padding:10px 12px;border-radius:6px;font-family:'Hanken Grotesk',sans-serif;font-weight:600;font-size:14px;text-transform:none;letter-spacing:0}
    .tb-tog{display:none;background:#e9b83f;border:2px solid #000;color:#1c1503;width:44px;height:44px;border-radius:6px;font-size:22px;cursor:pointer;box-shadow:3px 3px 0 #000;line-height:1}
    @media(max-width:860px){
        .tb-links{position:fixed;top:0;right:0;bottom:0;width:min(82vw,320px);flex-direction:column;align-items:stretch;gap:2px;background:#141418;border-left:2px solid #000;padding:84px 20px 30px;transform:translateX(100%);transition:.3s;z-index:70;overflow-y:auto}
        body.tb-open .tb-links{transform:translateX(0)}
        .tb-cta{margin-left:0;justify-content:center;text-align:center;margin-top:6px}
        .tb-sep{width:100%;height:1px;margin:8px 0}
        .tb-contact{justify-content:center;text-align:center;margin-top:2px}
        .tb-tog{display:grid;place-items:center;z-index:80}
        .tb-dd .mn{position:static;display:flex;border:none;box-shadow:none;background:transparent;padding:0 0 0 12px;min-width:0}
        .tb-dd > .lb{font-size:19px;padding:12px 14px 4px}
    }
</style>
<nav class="tbnav">
    <div class="tbnav-in">
        <a href="{{ route('home') }}" class="tb-brand">
            @if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $brand }}" style="height:42px;filter:brightness(0) invert(1)">
            @else<span class="mk">G</span><span class="wm">{{ $brand }}</span>@endif
        </a>
        <div class="tb-links" id="tbLinks">
            @foreach($navItems as $it)
                @php $isContact = trim((string) $it->url, '/') === 'contacto'; @endphp
                @if($isContact) @continue @endif
                @if($it->children->count())
                    <div class="tb-dd">
                        <span class="lb">{{ $it->label }} <span style="font-size:.7em">▾</span></span>
                        <div class="mn">@foreach($it->children as $ch)<a href="{{ $ch->url }}">{{ $ch->label }}</a>@endforeach</div>
                    </div>
                @else
                    <a href="{{ $it->url }}" class="{{ trim((string)$it->url,'/') === trim(request()->path(),'/') ? 'active' : '' }}">{{ $it->label }}</a>
                @endif
            @endforeach
            <span class="tb-sep"></span>
            <a href="{{ route('contacto') }}" class="tb-contact {{ request()->routeIs('contacto') ? 'active' : '' }}"><span class="ic">✉️</span> Contáctenos</a>
            <a href="{{ route('personalizar') }}" class="tb-cta">Diseña la tuya →</a>
        </div>
        <button class="tb-tog" aria-label="Menú" onclick="document.body.classList.toggle('tb-open')">☰</button>
    </div>
</nav>

@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
    $top = $entries->take(3);
    $rest = $entries->slice(3);
    $max = max(1, $entries->max('votes') ?? 1);
    $shareUrl = url()->current();
    $shareTxt = '¡Vota por mi colegio en el ranking de promociones Glauren! 🏆 '.$shareUrl;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'ranking',
    'seoTitleOverride' => '¿Qué colegio va ganando? · Ranking de promociones · Glauren',
    'seoDescriptionOverride' => 'Vota por tu colegio en el ranking de promociones 2026. ¡Haz que tu curso llegue al primer puesto!',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--gold2:#c9922e;--hot:#ff4a3d;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;padding-bottom:60px}
    a{text-decoration:none;color:inherit}
    .wrap{max-width:820px;margin:0 auto;padding:0 20px}
    .head{text-align:center;padding:44px 20px 10px}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.24em;font-size:12px;color:var(--gold2)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(36px,8vw,68px);line-height:.86;margin:8px 0 10px}
    h1 .hl{color:var(--gold)}
    .sub{color:var(--muted);max-width:520px;margin:0 auto}
    .flash{max-width:520px;margin:16px auto 0;background:rgba(37,211,102,.14);border:2px solid var(--ok);color:var(--ok);border-radius:10px;padding:11px 16px;font-weight:600;text-align:center}
    /* Podio */
    .podium{display:grid;grid-template-columns:1fr 1.2fr 1fr;gap:12px;align-items:end;margin:30px 0}
    .pod{background:var(--panel);border:2px solid #000;border-radius:14px;box-shadow:6px 6px 0 #000;padding:16px 12px;text-align:center;position:relative}
    .pod .medal{font-size:34px;line-height:1}
    .pod .nm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:17px;line-height:1;margin:8px 0 2px}
    .pod .ci{font-size:12px;color:var(--muted)}
    .pod .vt{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:30px;color:var(--gold);margin-top:6px;line-height:.9}
    .pod .vl{font-family:'Space Mono',monospace;font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}
    .pod.p1{border-color:var(--gold);box-shadow:6px 6px 0 var(--gold);transform:translateY(-10px)}
    .pod.p1 .plinth{height:70px} .pod.p2 .plinth{height:44px} .pod.p3 .plinth{height:30px}
    .plinth{background:repeating-linear-gradient(45deg,var(--panel2),var(--panel2) 8px,#2b2b34 8px,#2b2b34 16px);border-top:2px solid #000;border-radius:0 0 12px 12px;margin:12px -12px -16px;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;color:var(--muted)}
    .vbtn{display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:999px;padding:8px 16px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:14px;cursor:pointer;box-shadow:3px 3px 0 #000;transition:.1s}
    .vbtn:hover{transform:translate(1px,1px);box-shadow:2px 2px 0 #000}
    .vbtn.voted{background:var(--panel2);color:var(--muted);cursor:default;box-shadow:none;border-color:var(--line)}
    /* Lista */
    .list{margin-top:22px;display:flex;flex-direction:column;gap:10px}
    .row{display:flex;align-items:center;gap:14px;background:var(--panel);border:2px solid #000;border-radius:12px;box-shadow:4px 4px 0 #000;padding:12px 16px}
    .rk{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;color:var(--muted);width:34px;text-align:center;flex-shrink:0}
    .info{flex:1;min-width:0}
    .info .nm{font-weight:700}
    .info .ci{font-size:12px;color:var(--muted)}
    .track{height:8px;background:var(--panel2);border-radius:6px;overflow:hidden;margin-top:6px}
    .track > i{display:block;height:100%;background:linear-gradient(90deg,var(--gold),var(--gold2))}
    .cnt{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;color:var(--gold);width:52px;text-align:right;flex-shrink:0}
    /* Cajas */
    .card{background:var(--panel);border:2px solid #000;border-radius:14px;box-shadow:6px 6px 0 #000;padding:22px;margin-top:26px}
    .card h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;margin-bottom:6px}
    .card p{color:var(--muted);font-size:14px;margin-bottom:14px}
    .frm{display:flex;gap:10px;flex-wrap:wrap}
    .frm input{flex:1;min-width:150px;background:var(--panel2);border:2px solid #000;border-radius:9px;padding:12px;color:var(--paper);font-size:15px}
    .frm button{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:9px;padding:12px 20px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;cursor:pointer;box-shadow:4px 4px 0 #000}
    .share{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:22px}
    .share a{display:inline-flex;align-items:center;gap:8px;border:2px solid #000;border-radius:999px;padding:12px 22px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;box-shadow:4px 4px 0 #000}
    .share .wa{background:var(--ok);color:#04240f} .share .cp{background:var(--paper);color:#111}
    .empty{text-align:center;color:var(--muted);padding:26px}
    .backrow{text-align:center;margin-top:26px}.backrow a{font-family:'Space Mono',monospace;font-size:13px;color:var(--muted)}
</style>
</head>
<body>
    @include('chaquetas.partials.topbar')

    <div class="head">
        <div class="ey">// Promoción 2026</div>
        <h1>¿Qué colegio<br><span class="hl">va ganando?</span></h1>
        <p class="sub">Vota por tu colegio y hazlo subir al primer puesto. ¡Comparte con tu curso para sumar más votos! 🔥</p>
        @if(session('rank_msg'))<div class="flash">{{ session('rank_msg') }}</div>@endif
    </div>

    <div class="wrap">
        @if($entries->isEmpty())
            <div class="card"><div class="empty"><div style="font-size:36px">🏆</div><p class="mt-2">Aún no hay colegios en el ranking.<br>¡Sé el primero en agregar el tuyo!</p></div></div>
        @else
            {{-- Podio top 3 --}}
            @if($top->count() >= 2)
            <div class="podium">
                @php $order = [1,0,2]; $medals=['🥇','🥈','🥉']; @endphp
                @foreach($order as $pos)
                    @php $e = $top->get($pos); if(!$e) continue; $rankClass = 'p'.($pos+1); @endphp
                    <div class="pod {{ $rankClass }}">
                        <div class="medal">{{ $medals[$pos] }}</div>
                        <div class="nm">{{ $e->name }}</div>
                        @if($e->city)<div class="ci">{{ $e->city }}</div>@endif
                        <div class="vt">{{ number_format($e->votes,0,',','.') }}</div>
                        <div class="vl">votos</div>
                        <form method="POST" action="{{ route('ranking.vote', $e) }}" class="voteForm" data-id="{{ $e->id }}">@csrf
                            <button class="vbtn {{ isset($voted[$e->id]) ? 'voted' : '' }}" {{ isset($voted[$e->id]) ? 'disabled' : '' }}>▲ {{ isset($voted[$e->id]) ? 'Votado' : 'Votar' }}</button>
                        </form>
                        <div class="plinth">{{ $pos+1 }}°</div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Resto de la lista --}}
            <div class="list">
                @foreach(($top->count() >= 2 ? $rest : $entries) as $i => $e)
                    @php $rank = ($top->count() >= 2 ? 4 : 1) + $i; @endphp
                    <div class="row">
                        <div class="rk">{{ $rank }}</div>
                        <div class="info">
                            <div class="nm">{{ $e->name }}</div>
                            @if($e->city)<div class="ci">{{ $e->city }}</div>@endif
                            <div class="track"><i style="width:{{ (int) round($e->votes / $max * 100) }}%"></i></div>
                        </div>
                        <div class="cnt">{{ number_format($e->votes,0,',','.') }}</div>
                        <form method="POST" action="{{ route('ranking.vote', $e) }}" class="voteForm" data-id="{{ $e->id }}">@csrf
                            <button class="vbtn {{ isset($voted[$e->id]) ? 'voted' : '' }}" {{ isset($voted[$e->id]) ? 'disabled' : '' }}>▲ {{ isset($voted[$e->id]) ? 'Votado' : 'Votar' }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Agregar tu colegio --}}
        <div class="card">
            <h2>¿No está tu colegio?</h2>
            <p>Agrégalo y empieza con tu primer voto. Luego comparte para que tu curso vote también.</p>
            <form method="POST" action="{{ route('ranking.store') }}" class="frm">@csrf
                <input type="text" name="name" placeholder="Nombre del colegio" required maxlength="120">
                <input type="text" name="city" placeholder="Ciudad (opcional)" maxlength="80">
                <button type="submit">Agregar + votar</button>
            </form>
            @error('name')<div class="flash" style="background:rgba(255,74,61,.14);border-color:var(--hot);color:var(--hot)">{{ $message }}</div>@enderror
        </div>

        {{-- Compartir --}}
        <div class="share">
            <a class="wa" href="https://wa.me/?text={{ urlencode($shareTxt) }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Compartir en WhatsApp</a>
            <a class="cp" href="#" id="copyLink"><i class="bi bi-link-45deg"></i> Copiar link</a>
        </div>

        <div class="backrow"><a href="{{ route('home') }}">← Volver al inicio</a></div>
    </div>

<script>
    // Copiar link
    document.getElementById('copyLink')?.addEventListener('click', function(e){
        e.preventDefault();
        navigator.clipboard?.writeText(@json($shareUrl)).then(()=>{ this.innerHTML='<i class="bi bi-check2"></i> ¡Link copiado!'; setTimeout(()=>{ this.innerHTML='<i class="bi bi-link-45deg"></i> Copiar link'; },2000); });
    });
</script>
</body>
</html>

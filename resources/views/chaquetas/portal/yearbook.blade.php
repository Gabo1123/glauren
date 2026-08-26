@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); $c = $student->contract; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Anuario · {{ $c?->school?->name ?? $brand }}</title><meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#17171d;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--muted:#9a9aa7;--line:rgba(244,239,227,.1)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif}
    .top{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--line)}
    .top a{color:var(--muted);text-decoration:none;font-size:14px}.top a:hover{color:var(--gold)}
    .wrap{max-width:820px;margin:0 auto;padding:0 16px 60px}
    .hero{text-align:center;padding:44px 16px 28px;background:radial-gradient(600px 240px at 50% -20%,rgba(233,184,63,.28),transparent 60%)}
    .hero .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:12px;color:var(--gold)}
    .hero h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(34px,7vw,60px);line-height:.9;margin:8px 0}
    .hero p{color:#cfc9bd}
    .sec-t{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:20px;margin:30px 0 14px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px}
    .mate{background:var(--panel);border:1px solid var(--line);border-radius:14px;overflow:hidden;text-align:center}
    .mate .ph{aspect-ratio:1;background:rgba(233,184,63,.1);display:grid;place-items:center;font-size:48px;overflow:hidden}
    .mate .ph img{width:100%;height:100%;object-fit:cover}
    .mate .info{padding:10px}
    .mate b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:16px;display:block}
    .mate span{color:var(--muted);font-size:12px}
    .mate .bio{color:#cfc9bd;font-size:12px;margin-top:4px;font-style:italic}
    .album{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px}
    .album a{display:block;aspect-ratio:1;border-radius:10px;overflow:hidden;border:1px solid var(--line)}
    .album img{width:100%;height:100%;object-fit:cover}
    .empty{color:var(--muted);text-align:center;padding:20px 0;font-size:14px}
    @media print{.top,.noprint{display:none}body{background:#fff;color:#111}.hero{background:none}}
</style>
</head>
<body>
    <div class="top">
        <a href="{{ route('student.home') }}">← Mi portal</a>
        <a href="#" onclick="window.print();return false" class="noprint">🖨️ Imprimir / Guardar PDF</a>
    </div>

    <div class="hero">
        <div class="ey">// Promoción {{ $c?->year }}</div>
        <h1>Anuario<br>{{ $c?->school?->name ?? 'de nuestro curso' }}</h1>
        <p>{{ $classmates->count() }} compañeros · {{ $albumPhotos->count() }} recuerdos 🎓</p>
    </div>

    <div class="wrap">
        {{-- Compañeros --}}
        <div class="sec-t">🎓 La promoción</div>
        <div class="grid">
            @foreach($classmates as $cm)
                <div class="mate">
                    <div class="ph">@if($cm->photoUrl())<img src="{{ $cm->photoUrl() }}" alt="{{ $cm->name }}">@else🙂@endif</div>
                    <div class="info">
                        <b>{{ $cm->nickname ?: explode(' ',$cm->name)[0] }}</b>
                        <span>{{ $cm->name }}</span>
                        @if($cm->bio)<div class="bio">“{{ $cm->bio }}”</div>@endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Álbum de fotos --}}
        <div class="sec-t">📸 Álbum del curso</div>
        @if($albumPhotos->count())
            <div class="album">
                @foreach($albumPhotos as $p)
                    <a href="{{ $p->photoUrl() }}" target="_blank"><img src="{{ $p->photoUrl() }}" alt="Recuerdo" loading="lazy"></a>
                @endforeach
            </div>
        @else
            <div class="empty">Aún no hay fotos. Suban fotos al muro y aparecerán aquí. 📷</div>
        @endif
    </div>
</body>
</html>

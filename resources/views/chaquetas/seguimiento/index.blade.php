@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Busca tu chaqueta · Glauren</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--blast:#e9b83f;--gold:#e9b83f;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:flex;flex-direction:column;
        background-image:radial-gradient(900px 500px at 85% -10%,rgba(43,52,255,.18),transparent 60%)}
    a{text-decoration:none}
    .top{display:flex;align-items:center;justify-content:space-between;padding:16px 24px}
    .brand{display:flex;align-items:center;gap:11px;color:var(--paper)}
    .brand .mk{width:40px;height:40px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:22px;letter-spacing:.04em}
    .back-top{font-family:'Space Mono',monospace;font-size:13px;color:var(--muted)} .back-top:hover{color:var(--gold)}
    .mid{flex:1;display:grid;place-items:center;padding:24px}
    .card{max-width:480px;width:100%;background:var(--panel);border:3px solid #000;border-radius:14px;padding:34px;box-shadow:10px 10px 0 var(--blast)}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--blast)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:46px;line-height:.86;margin:10px 0}
    p.sub{color:var(--muted);font-size:15px;margin-bottom:24px}
    label{font-family:'Space Mono',monospace;font-size:12px;color:var(--muted);display:block;margin-bottom:7px;text-transform:uppercase;letter-spacing:.05em}
    input{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);border-radius:8px;padding:15px;font-size:18px;font-family:'Space Mono',monospace}
    input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.25)}
    .btn{width:100%;margin-top:16px;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:15px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:20px;cursor:pointer;box-shadow:5px 5px 0 #000;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:11px 13px;font-size:13px;margin-bottom:18px}
    .foot{display:flex;justify-content:space-between;align-items:center;margin-top:20px;font-size:13px;color:var(--muted);flex-wrap:wrap;gap:10px}
    .foot a{color:var(--muted)} .foot a:hover{color:var(--gold)}
    .wa{color:#25d366;font-weight:600}
</style>
</head>
<body>
    <div class="top">
        <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">Glauren</span></a>
        <a href="{{ route('home') }}" class="back-top">← Inicio</a>
    </div>
    <div class="mid">
        <div class="card">
            <span class="ey">// Seguimiento · Grado once</span>
            <h1>Busca tu<br>chaqueta</h1>
            <p class="sub">Escribe tu número de tarjeta de identidad o cédula y mira tu contrato, el avance de fabricación con fotos reales y tu pago.</p>
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('seguimiento.search') }}">
                @csrf
                <label for="document">Tarjeta de identidad / cédula</label>
                <input type="text" id="document" name="document" value="{{ old('document') }}" placeholder="Ej: 1010101010" inputmode="numeric" autofocus>
                <button class="btn">Ver mi chaqueta →</button>
            </form>
            <div class="foot">
                <span>¿No apareces? <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, busqué mi chaqueta con mi documento y no aparezco.') }}" target="_blank" rel="noopener" class="wa">Escríbenos</a></span>
                <a href="{{ route('portal.login') }}">Soy un colegio →</a>
            </div>
        </div>
    </div>
</body>
</html>

@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro · {{ $contract->school?->name }}</title><meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:22px;background-image:radial-gradient(900px 460px at 80% -10%,rgba(233,184,63,.16),transparent 60%)}
    .card{width:100%;max-width:420px;background:var(--panel);border:3px solid #000;border-radius:16px;padding:30px 26px;box-shadow:10px 10px 0 var(--gold)}
    .brand{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;color:var(--gold);font-size:24px;text-align:center;margin-bottom:4px}
    .tag{font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:var(--muted);text-align:center;margin-bottom:20px}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:24px;line-height:.95;margin-bottom:6px}
    p.sub{color:var(--muted);font-size:14px;margin-bottom:18px}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin:12px 0 5px;text-transform:uppercase}
    input,select{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:12px;border-radius:8px;font-size:15px}
    input:focus,select:focus{outline:none;border-color:var(--gold)}
    .btn{width:100%;background:var(--ok);color:#04240f;border:2px solid #000;border-radius:8px;padding:14px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:18px;cursor:pointer;box-shadow:5px 5px 0 #000;margin-top:16px}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:10px;font-size:13px;margin-bottom:14px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
</style>
</head>
<body>
    <div class="card">
        <div class="brand">{{ $brand }}</div>
        <div class="tag">{{ $contract->school?->name }} · Prom {{ $contract->year }}</div>

        @if(!empty($done))
            <div style="text-align:center">
                <div style="font-size:48px">🎉</div>
                <h1 style="text-align:center">¡Listo, {{ explode(' ',$name)[0] }}!</h1>
                <p class="sub" style="text-align:center">Quedaste registrado en la promoción. Pronto tomaremos tus medidas y podrás seguir tu chaqueta.</p>
                <a href="{{ route('seguimiento') }}" class="btn" style="text-decoration:none;display:block">Seguir mi chaqueta →</a>
            </div>
        @else
            <h1>Regístrate en tu promoción</h1>
            <p class="sub">Completa tus datos para unirte a las chaquetas de <b>{{ $contract->jacketType?->name ?? 'promoción' }}</b> de tu curso.</p>
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('join.store', $contract->join_token) }}">
                @csrf
                <label>Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
                <div class="row">
                    <div><label>Documento *</label><input type="text" name="document" value="{{ old('document') }}" inputmode="numeric" required></div>
                    <div><label>Sexo</label><select name="gender"><option value="">—</option><option value="hombre">Hombre</option><option value="mujer">Mujer</option></select></div>
                </div>
                <div class="row">
                    <div><label>WhatsApp</label><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="300 123 4567"></div>
                    <div><label>Talla (si sabes)</label><input type="text" name="size" value="{{ old('size') }}" placeholder="S / M / L"></div>
                </div>
                <button class="btn">Registrarme →</button>
            </form>
        @endif
    </div>
</body>
</html>

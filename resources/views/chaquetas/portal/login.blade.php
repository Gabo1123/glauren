@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); $step = $step ?? 'doc'; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi portal · {{ $brand }}</title><meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;background-image:radial-gradient(900px 500px at 80% -10%,rgba(233,184,63,.16),transparent 60%)}
    .card{width:100%;max-width:400px;background:var(--panel);border:3px solid #000;border-radius:16px;padding:34px 30px;box-shadow:10px 10px 0 var(--gold)}
    .brand{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:26px;color:var(--gold);text-align:center}
    .tag{font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);text-align:center;margin:6px 0 22px}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:24px;margin-bottom:6px;text-align:center}
    p.sub{color:var(--muted);font-size:14px;text-align:center;margin-bottom:20px}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em}
    input{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:13px;border-radius:8px;margin-bottom:14px;font-size:16px}
    input.pin{text-align:center;letter-spacing:.5em;font-size:26px;font-family:'Space Mono',monospace}
    input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.22)}
    .btn{width:100%;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:13px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:18px;cursor:pointer;box-shadow:5px 5px 0 #000}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:10px;font-size:13px;margin-bottom:14px}
    .back{display:block;text-align:center;margin-top:18px;color:var(--muted);text-decoration:none;font-size:13px;font-family:'Space Mono',monospace}
    .back:hover{color:var(--gold)}
</style>
</head>
<body>
    <div class="card">
        <div class="brand">{{ $brand }}</div>
        <div class="tag">Portal del estudiante</div>

        @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif

        @if($step === 'doc')
            <h1>Entra a tu portal</h1>
            <p class="sub">Tu perfil, tus fotos y el muro de tu curso 🎓</p>
            <form method="POST" action="{{ route('student.doc') }}">
                @csrf
                <label>Tu número de documento</label>
                <input type="text" name="document" value="{{ old('document') }}" inputmode="numeric" required autofocus placeholder="Ej: 1012345678">
                <button class="btn">Continuar →</button>
            </form>
        @elseif($step === 'create')
            <h1>¡Hola, {{ $student->nickname ?: explode(' ', $student->name)[0] }}!</h1>
            <p class="sub">Crea tu PIN de 4 números para proteger tu perfil.</p>
            <form method="POST" action="{{ route('student.auth') }}">
                @csrf
                <label>Crea tu PIN (4 números)</label>
                <input type="password" name="pin" class="pin" inputmode="numeric" maxlength="4" required autofocus placeholder="••••">
                <label>Repite tu PIN</label>
                <input type="password" name="pin2" class="pin" inputmode="numeric" maxlength="4" required placeholder="••••">
                <button class="btn">Crear y entrar →</button>
            </form>
        @elseif($step === 'reset')
            <h1>Restablecer PIN</h1>
            <p class="sub">Escribe el código que te llegó por WhatsApp @if(!empty($phoneHint))(a {{ $phoneHint }})@endif y crea un PIN nuevo.</p>
            @if(session('ok'))<div class="err" style="background:rgba(37,211,102,.12);border-color:#25d366;color:#bff5d0">✅ {{ session('ok') }}</div>@endif
            <form method="POST" action="{{ route('student.reset') }}">
                @csrf
                <label>Código (4 números)</label>
                <input type="text" name="code" class="pin" inputmode="numeric" maxlength="4" required autofocus placeholder="••••">
                <label>Tu nuevo PIN</label>
                <input type="password" name="pin" class="pin" inputmode="numeric" maxlength="4" required placeholder="••••">
                <label>Repite el nuevo PIN</label>
                <input type="password" name="pin2" class="pin" inputmode="numeric" maxlength="4" required placeholder="••••">
                <button class="btn">Guardar y entrar →</button>
            </form>
        @else
            <h1>¡Hola de nuevo!</h1>
            <p class="sub">Ingresa tu PIN de 4 números.</p>
            <form method="POST" action="{{ route('student.auth') }}">
                @csrf
                <label>Tu PIN</label>
                <input type="password" name="pin" class="pin" inputmode="numeric" maxlength="4" required autofocus placeholder="••••">
                <button class="btn">Entrar →</button>
            </form>
            <form method="POST" action="{{ route('student.forgot') }}" style="margin-top:12px;text-align:center">
                @csrf
                <button type="submit" class="back" style="background:none;border:0;cursor:pointer">¿Olvidaste tu PIN? Recupéralo por WhatsApp</button>
            </form>
        @endif

        <a href="{{ route('home') }}" class="back">← Volver al sitio</a>
    </div>
</body>
</html>

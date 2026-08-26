<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración · Glauren</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--gold2:#f2cf63;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--ink);color:var(--paper);font-family:var(--g-font-body),'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;
            background-image:radial-gradient(1000px 560px at 82% -10%,rgba(233,184,63,.16),transparent 60%)}
        .card{width:100%;max-width:400px;background:var(--panel);border:3px solid #000;border-radius:14px;padding:36px 32px;box-shadow:10px 10px 0 var(--gold)}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:4px}
        .brand .mk{width:44px;height:44px;border-radius:10px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:var(--g-font-display),sans-serif;font-weight:900;font-size:24px;border:2px solid #000;box-shadow:3px 3px 0 #000}
        .brand .wm{font-family:var(--g-font-display),sans-serif;font-weight:800;text-transform:uppercase;font-size:26px;letter-spacing:.04em;color:var(--paper);line-height:.85}
        .tag{font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin:12px 0 26px}
        label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em}
        input[type=email],input[type=password]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:12px 13px;border-radius:8px;margin-bottom:16px;font-size:15px}
        input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.22)}
        .rem{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:18px}
        .btn{width:100%;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:13px;font-family:var(--g-font-display),sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:19px;cursor:pointer;box-shadow:5px 5px 0 #000;transition:transform .1s,box-shadow .1s}
        .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
        .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:18px}
        .back{display:block;text-align:center;margin-top:20px;color:var(--muted);text-decoration:none;font-size:13px;font-family:'Space Mono',monospace}
        .back:hover{color:var(--gold)}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand"><span class="mk">G</span><span class="wm">Glauren</span></div>
        <div class="tag">Panel administrativo</div>
        @if($errors->any())<div class="err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            <label>Contraseña</label>
            <input type="password" name="password" required>
            <label class="rem"><input type="checkbox" name="remember" style="width:auto;margin:0"> Recordarme</label>
            <button class="btn">Ingresar</button>
        </form>
        <a href="{{ route('home') }}" class="back">← Volver al sitio</a>
    </div>
</body>
</html>

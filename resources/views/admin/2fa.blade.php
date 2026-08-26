<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación · Glauren</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7}
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--ink);color:var(--paper);font-family:var(--g-font-body),'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;
            background-image:radial-gradient(1000px 560px at 82% -10%,rgba(233,184,63,.16),transparent 60%)}
        .card{width:100%;max-width:400px;background:var(--panel);border:3px solid #000;border-radius:14px;padding:36px 32px;box-shadow:10px 10px 0 var(--gold)}
        .ic{width:54px;height:54px;border-radius:12px;background:rgba(37,211,102,.14);border:2px solid var(--ok);display:grid;place-items:center;font-size:26px;margin-bottom:16px}
        h1{font-family:var(--g-font-display),sans-serif;font-weight:800;text-transform:uppercase;font-size:26px;letter-spacing:.02em;margin-bottom:6px}
        p.sub{color:var(--muted);font-size:14px;margin-bottom:22px}
        label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em}
        input[type=text]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:16px;border-radius:8px;margin-bottom:16px;font-size:30px;letter-spacing:.4em;text-align:center;font-family:'Space Mono',monospace}
        input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.22)}
        .btn{width:100%;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:13px;font-family:var(--g-font-display),sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:19px;cursor:pointer;box-shadow:5px 5px 0 #000;transition:transform .1s,box-shadow .1s}
        .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
        .ok{background:rgba(37,211,102,.12);color:#bff5d0;border:2px solid var(--ok);border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:16px}
        .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:16px}
        .row{display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:13px;font-family:'Space Mono',monospace}
        .row a,.row button{color:var(--muted);text-decoration:none;background:none;border:0;cursor:pointer;font-family:inherit;font-size:13px}
        .row a:hover,.row button:hover{color:var(--gold)}
    </style>
</head>
<body>
    <div class="card">
        <div class="ic">🔐</div>
        <h1>Verificación</h1>
        <p class="sub">Enviamos un código de 6 dígitos por WhatsApp @if($phoneHint)al número <b>{{ $phoneHint }}</b>@endif. Ingrésalo para entrar.</p>

        @if(session('ok'))<div class="ok"><i class="bi bi-whatsapp"></i> {{ session('ok') }}</div>@endif
        @if($errors->any())<div class="err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('admin.2fa.verify') }}">
            @csrf
            <label>Código de acceso</label>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]*" placeholder="000000" required autofocus>
            <button class="btn">Verificar y entrar</button>
        </form>

        <div class="row">
            <form method="POST" action="{{ route('admin.2fa.resend') }}">@csrf<button type="submit">↻ Reenviar código</button></form>
            <a href="{{ route('admin.login') }}">← Cancelar</a>
        </div>
    </div>
</body>
</html>

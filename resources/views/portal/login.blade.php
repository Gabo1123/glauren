<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal colegios · Glauren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        :root{
            --g-ink:#0d0d0f; --g-surface:#1a1a20; --g-surface-2:#22222a;
            --g-bone:#f4efe3; --g-gold:#e9b83f; --g-gold-2:#f2cf63; --g-muted:#9a9aa7;
            --g-line:rgba(244,239,227,.14); --blast:#e9b83f;
        }
        body{ background:var(--g-ink); color:var(--g-bone); font-family:var(--g-font-body); margin:0;
            min-height:100vh; display:grid; place-items:center; padding:24px;
            background-image:radial-gradient(1000px 560px at 82% -10%, rgba(43,52,255,.18), transparent 60%); }
        .card{ box-shadow:10px 10px 0 var(--blast) !important; border:3px solid #000 !important; }
        .brand .mk{ border-radius:9px !important; background:var(--g-gold) !important; color:#1c1503 !important; border:2px solid #000 !important; box-shadow:2px 2px 0 #000; font-weight:900; }
        .btn{ border:2px solid #000 !important; box-shadow:4px 4px 0 #000; font-weight:800 !important; }
        .card{ width:100%; max-width:400px; background:var(--g-surface); border:1px solid var(--g-line); border-radius:var(--g-r); padding:34px 30px; }
        .brand{ font-family:var(--g-font-display); text-transform:uppercase; letter-spacing:.05em; font-size:24px; display:flex; align-items:center; gap:11px; margin-bottom:4px; }
        .brand .mk{ width:30px;height:30px;border:2px solid var(--g-gold);border-radius:50%;display:grid;place-items:center;color:var(--g-gold);font-size:13px;font-family:var(--g-font-display); }
        .tag{ font-family:var(--g-font-label); font-size:10px; letter-spacing:.22em; text-transform:uppercase; color:var(--g-muted); margin-bottom:26px; }
        label{ font-size:12px; color:var(--g-muted); display:block; margin-bottom:6px; letter-spacing:.03em; }
        input{ width:100%; background:var(--g-surface-2); border:1px solid var(--g-line); color:var(--g-bone); padding:11px 13px; border-radius:var(--g-r-sm); margin-bottom:16px; box-sizing:border-box; font-size:15px; }
        input:focus{ outline:none; border-color:var(--g-gold); box-shadow:0 0 0 3px rgba(201,165,75,.12); }
        .btn{ width:100%; background:var(--g-gold); color:#1a1405; border:none; padding:12px; border-radius:var(--g-r-sm); font-weight:700; cursor:pointer; font-size:15px; letter-spacing:.02em; }
        .btn:hover{ background:var(--g-gold-2); }
        .err{ background:rgba(224,60,60,.12); border:1px solid rgba(224,60,60,.3); color:#f0a3a3; padding:10px 13px; border-radius:var(--g-r-sm); margin-bottom:18px; font-size:13px; }
        .back{ display:block; text-align:center; margin-top:20px; color:var(--g-muted); text-decoration:none; font-size:13px; }
        .back:hover{ color:var(--g-gold); }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand"><span class="mk">G</span> Glauren</div>
        <div class="tag">Portal para colegios</div>
        @if($errors->any())<div class="err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('portal.login.post') }}">
            @csrf
            <label>Correo del colegio</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            <label>Contraseña</label>
            <input type="password" name="password" required>
            <button class="btn">Entrar</button>
        </form>
        <a href="{{ route('home') }}" class="back">← Volver al sitio</a>
    </div>
</body>
</html>

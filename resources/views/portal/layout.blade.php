<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal colegios · @yield('title', 'Glauren')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        /* Paleta streetwear (sobrescribe los tokens del sistema para el portal) */
        :root{
            --g-ink:#0d0d0f; --g-surface:#1a1a20; --g-surface-2:#22222a; --g-surface-3:#2b2b34;
            --g-bone:#f4efe3; --g-gold:#e9b83f; --g-gold-2:#f2cf63; --g-muted:#9a9aa7;
            --g-line:rgba(244,239,227,.14); --blast:#e9b83f;
        }
        body{ background:var(--g-ink); color:var(--g-bone); font-family:var(--g-font-body); margin:0;
            background-image:radial-gradient(900px 500px at 90% -10%,rgba(43,52,255,.12),transparent 60%); }
        .p-top{ position:sticky; top:0; z-index:30; display:flex; align-items:center; justify-content:space-between;
            padding:13px 26px; background:rgba(13,13,15,.94); backdrop-filter:blur(10px); border-bottom:2px solid #000; }
        .p-brand{ font-family:var(--g-font-display); font-weight:800; text-transform:uppercase; letter-spacing:.04em; font-size:21px; display:flex; align-items:center; gap:11px; color:var(--g-bone); text-decoration:none; }
        .p-brand .mk{ width:34px;height:34px;background:var(--g-gold);border:2px solid #000;border-radius:8px;box-shadow:2px 2px 0 #000;display:grid;place-items:center;color:#1c1503;font-size:18px;font-weight:900;font-family:var(--g-font-display); }
        .p-who{ display:flex; align-items:center; gap:16px; font-size:14px; color:var(--g-muted); }
        .p-who b{ color:var(--g-bone); font-weight:600; }
        .p-wrap{ max-width:1040px; margin:0 auto; padding:34px 22px 80px; }
        .p-logout button{ background:none; border:1px solid var(--g-line); color:var(--g-muted); padding:7px 13px; border-radius:var(--g-r-sm); cursor:pointer; font-size:13px; transition:var(--g-t); }
        .p-logout button:hover{ border-color:var(--g-gold); color:var(--g-gold); }
        h1.p-h{ font-family:var(--g-font-display); text-transform:uppercase; letter-spacing:.02em; font-size:30px; margin:0 0 6px; }
        .p-sub{ color:var(--g-muted); margin:0 0 26px; }
        .flash{ background:rgba(37,211,102,.12); border:1px solid rgba(37,211,102,.3); color:var(--g-ok); padding:11px 16px; border-radius:var(--g-r-sm); margin-bottom:20px; font-size:14px; }
        a{ color:var(--g-gold); }
        @media(max-width:600px){ .p-who .hide-sm{ display:none; } .p-wrap{ padding:22px 16px 60px; } }
    </style>
</head>
<body>
<div class="p-top">
    <a href="{{ route('portal.dashboard') }}" class="p-brand"><span class="mk">G</span> Glauren</a>
    <div class="p-who">
        <span class="hide-sm"><i class="bi bi-building"></i> <b>{{ auth()->user()->school?->name }}</b></span>
        <form method="POST" action="{{ route('portal.logout') }}" class="p-logout">@csrf
            <button><i class="bi bi-box-arrow-right"></i> Salir</button>
        </form>
    </div>
</div>
<main class="p-wrap">
    @if(session('ok'))<div class="flash"><i class="bi bi-check-circle"></i> {{ session('ok') }}</div>@endif
    @yield('content')
</main>
</body>
</html>

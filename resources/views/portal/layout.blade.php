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
        /* ===== Contrato: encabezado, cifras y pestañas ===== */
        .pc-back{ color:var(--g-muted); text-decoration:none; font-size:14px; }
        .pc-hero{ display:flex; gap:22px; flex-wrap:wrap; justify-content:space-between; margin:14px 0 18px; padding:24px;
            background:linear-gradient(135deg,#1d1d24,#16161b); border:1px solid var(--g-line); border-radius:18px; position:relative; overflow:hidden; }
        .pc-hero:before{ content:""; position:absolute; right:-60px; top:-60px; width:220px; height:220px; border-radius:50%; background:radial-gradient(circle,rgba(233,184,63,.22),transparent 70%); }
        .pc-hero-main{ flex:1 1 420px; min-width:0; position:relative; }
        .pc-kick{ font-size:11px; letter-spacing:.18em; color:var(--g-gold); font-weight:700; }
        .pc-sub{ color:var(--g-muted); font-size:14px; }
        .pc-chips{ display:flex; gap:8px; flex-wrap:wrap; margin:14px 0 16px; }
        .pc-chip{ font-size:12px; font-weight:600; padding:5px 11px; border-radius:999px; border:1px solid var(--g-line); color:var(--g-bone); }
        .pc-chip.gold{ border-color:rgba(233,184,63,.5); color:var(--g-gold); background:rgba(233,184,63,.08); }
        .pc-chip.ok{ border-color:rgba(37,211,102,.45); color:var(--g-ok); background:rgba(37,211,102,.08); }
        .pc-chip.warn{ border-color:rgba(224,164,34,.5); color:var(--g-warn); background:rgba(224,164,34,.08); }
        .pc-prog-top{ display:flex; justify-content:space-between; font-size:12px; color:var(--g-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
        .pc-prog-top b{ color:var(--g-gold); font-size:14px; }
        .pc-bar{ height:10px; background:var(--g-surface-2); border-radius:999px; overflow:hidden; }
        .pc-bar i{ display:block; height:100%; background:linear-gradient(90deg,var(--g-gold),#ff8a3d); border-radius:999px; }
        .pc-prog-foot{ font-size:13px; color:var(--g-muted); margin-top:8px; }
        .pc-prog-foot b{ color:var(--g-bone); }
        .pc-hero-act{ display:flex; flex-direction:column; gap:8px; justify-content:center; min-width:220px; position:relative; }
        .pc-btn{ display:flex; align-items:center; gap:9px; padding:11px 16px; border-radius:12px; border:1px solid var(--g-line); background:var(--g-surface-2); color:var(--g-bone); text-decoration:none; font-size:14px; font-weight:600; transition:var(--g-t); }
        .pc-btn:hover{ border-color:var(--g-gold); color:var(--g-gold); }
        .pc-btn.gold{ background:var(--g-gold); border-color:var(--g-gold); color:#1a1405; }
        .pc-btn.gold:hover{ color:#1a1405; filter:brightness(1.07); }
        .pc-kpis{ display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
        .pc-kpi{ background:var(--g-surface); border:1px solid var(--g-line); border-radius:14px; padding:14px 16px; display:grid; grid-template-columns:auto 1fr; column-gap:12px; align-items:center; }
        .pc-kpi i{ grid-row:span 2; font-size:22px; color:var(--g-gold); }
        .pc-kpi b{ font-family:var(--g-font-display); font-size:24px; line-height:1; }
        .pc-kpi span{ color:var(--g-muted); font-size:11px; text-transform:uppercase; letter-spacing:.06em; }
        .pc-kpi.ok i,.pc-kpi.ok b{ color:var(--g-ok); }
        .pc-kpi.bad i,.pc-kpi.bad b{ color:#ff6b60; }
        .pc-alert{ display:block; text-decoration:none; border-radius:12px; padding:12px 16px; margin-bottom:14px; font-size:14px; }
        .pc-alert.warn{ background:rgba(224,164,34,.1); border:1px solid rgba(224,164,34,.35); color:var(--g-warn); }
        .pc-tabs{ position:sticky; top:62px; z-index:20; display:flex; gap:6px; overflow-x:auto; padding:8px; margin:0 0 20px; background:rgba(13,13,15,.92); backdrop-filter:blur(8px); border:1px solid var(--g-line); border-radius:14px; scrollbar-width:none; }
        .pc-tabs::-webkit-scrollbar{ display:none; }
        .pc-tabs a{ flex:0 0 auto; display:flex; align-items:center; gap:7px; padding:9px 14px; border-radius:10px; color:var(--g-muted); text-decoration:none; font-size:14px; font-weight:600; white-space:nowrap; transition:var(--g-t); }
        .pc-tabs a:hover{ color:var(--g-bone); background:var(--g-surface-2); }
        .pc-tabs a.on{ background:var(--g-gold); color:#1a1405; }
        .pc-tabs em{ font-style:normal; font-size:11px; background:var(--g-surface-3); color:var(--g-bone); padding:1px 7px; border-radius:999px; }
        .pc-tabs a.on em{ background:rgba(0,0,0,.18); color:#1a1405; }
        .pc-tabs em.bad{ background:#ff6b60; color:#fff; }
        .pc-grid2{ display:grid; grid-template-columns:1.2fr 1fr; gap:22px; align-items:start; }
        .pc-grid2>section{ margin-bottom:22px!important; }
        @media(max-width:860px){ .pc-kpis{ grid-template-columns:repeat(2,1fr); } .pc-grid2{ grid-template-columns:1fr; gap:0; } .pc-hero-act{ min-width:0; flex:1 1 100%; flex-direction:row; flex-wrap:wrap; } .pc-hero-act .pc-btn{ flex:1 1 45%; justify-content:center; } }
        @media(max-width:600px){ .p-who .hide-sm{ display:none; } .p-wrap{ padding:22px 16px 60px; } .pc-tabs{ top:58px; } }
        @media(max-width:600px){
            .pc-pane [style*="grid-template-columns:repeat(4,1fr)"]{ grid-template-columns:repeat(2,1fr)!important; }
            .pc-pane [style*="grid-template-columns:repeat(3,1fr)"]{ grid-template-columns:1fr!important; }
        }
        .remind{ display:inline-flex; align-items:center; gap:7px; background:var(--g-surface-2); border:1px solid var(--g-line); border-radius:999px; padding:6px 8px 6px 12px; font-size:12.5px; color:var(--g-bone); text-decoration:none; transition:var(--g-t); }
        .remind i{ color:#25d366; }
        .remind span{ background:#25d366; color:#04240f; font-weight:700; font-size:11px; border-radius:999px; padding:2px 8px; }
        .remind:hover{ border-color:#25d366; }
        .ph-edit summary::-webkit-details-marker{ display:none; }
        /* ===== Inicio del portal ===== */
        .pd-hero{ display:flex; gap:20px; align-items:center; flex-wrap:wrap; padding:24px; margin-bottom:18px; border-radius:18px; border:1px solid var(--g-line);
            background:radial-gradient(500px 220px at 0% 0%,rgba(233,184,63,.18),transparent 70%),linear-gradient(135deg,#1d1d24,#16161b); }
        .pd-av{ flex:0 0 64px; height:64px; border-radius:18px; background:var(--g-gold); color:#1a1405; display:grid; place-items:center; font-family:var(--g-font-display); font-size:32px; font-weight:900; border:2px solid #000; box-shadow:3px 3px 0 #000; }
        .pd-next{ margin-left:auto; display:flex; align-items:center; gap:14px; background:rgba(233,184,63,.1); border:1.5px solid rgba(233,184,63,.5); border-radius:14px; padding:12px 16px; text-decoration:none; color:var(--g-bone); max-width:420px; }
        .pd-next small{ display:block; font-size:10.5px; letter-spacing:.14em; color:var(--g-gold); font-weight:700; }
        .pd-next b{ display:block; font-size:15px; }
        .pd-next span{ font-size:12.5px; color:var(--g-muted); }
        .pd-next i.go{ font-size:20px; color:var(--g-gold); }
        .pd-sec{ font-family:var(--g-font-display); text-transform:uppercase; font-size:16px; letter-spacing:.05em; margin:26px 0 12px; display:flex; align-items:center; gap:8px; }
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

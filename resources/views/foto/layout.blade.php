<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'Panel de Fotografía')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    :root{ --ink:#12100f; --panel:#1b1917; --line:#2e2a27; --bone:#f4f1ea; --muted:#a79f97; --gold:#c9a54b; }
    body{ background:var(--ink); color:var(--bone); font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; }
    a{ color:var(--gold); }
    .fp-top{ background:var(--panel); border-bottom:1px solid var(--line); padding:12px 18px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; position:sticky; top:0; z-index:20 }
    .fp-top .brand{ font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--gold) }
    .fp-wrap{ max-width:1050px; margin:0 auto; padding:22px 16px 60px }
    .card{ background:var(--panel); border:1px solid var(--line); color:var(--bone); border-radius:14px }
    .form-control, .form-select, textarea{ background:var(--ink); border:1px solid var(--line); color:var(--bone) }
    .form-control:focus, .form-select:focus, textarea:focus{ background:var(--ink); color:var(--bone); border-color:var(--gold); box-shadow:0 0 0 .2rem rgba(201,165,75,.2) }
    .form-label{ color:var(--muted); font-size:13px; margin-bottom:4px }
    .btn-gold{ background:var(--gold); color:#1a1405; font-weight:700; border:0 }
    .btn-gold:hover{ background:#dbb75e; color:#1a1405 }
    .text-muted2{ color:var(--muted) }
    .chip{ background:var(--ink); border:1px solid var(--line); border-radius:999px; padding:3px 10px; font-size:12px; color:var(--muted) }
    .srv{ border:1px solid var(--line); border-radius:10px; padding:10px 12px; margin-bottom:8px; display:flex; justify-content:space-between; gap:10px; align-items:center }
</style>
</head>
<body>
<div class="fp-top">
    <span class="brand">📸 Fotografía de Grados</span>
    <a href="{{ route('foto.dashboard') }}" class="text-decoration-none text-muted2">Panel</a>
    <a href="{{ route('fotografia') }}" target="_blank" class="text-decoration-none text-muted2">Ver página pública ↗</a>
    <form method="POST" action="{{ route('foto.logout') }}" class="ms-auto">@csrf<button class="btn btn-sm btn-outline-light">Salir</button></form>
</div>
<div class="fp-wrap">
    @if(session('ok'))<div class="alert alert-success py-2">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @yield('content')
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · Chaquetas de Promoción</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500&display=swap" rel="stylesheet">
<style>
    :root{--ink:#080b14;--bone:#efe7d6;--gold:#c9a54b;--muted:#8b93a7;--line:rgba(239,231,214,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--bone);font-family:'Inter',sans-serif;min-height:100vh;display:grid;place-items:center;text-align:center;padding:24px}
    .eyebrow{font-family:'Space Grotesk',sans-serif;text-transform:uppercase;letter-spacing:.3em;font-size:12px;color:var(--gold)}
    h1{font-family:'Anton',sans-serif;text-transform:uppercase;font-size:clamp(36px,7vw,72px);line-height:.95;margin:16px 0 18px}
    p{color:var(--muted);max-width:520px;margin:0 auto 30px}
    .btn{display:inline-flex;gap:8px;padding:14px 24px;border:1px solid var(--line);border-radius:2px;color:var(--bone);text-decoration:none;font-weight:600;transition:.18s}
    .btn:hover{border-color:var(--gold);color:var(--gold)}
    .tag{margin-top:26px;font-family:'Space Grotesk',sans-serif;font-size:12px;color:var(--muted);letter-spacing:.15em;text-transform:uppercase}
</style>
</head>
<body>
    <div>
        <span class="eyebrow">Próximamente</span>
        <h1>{{ $title }}</h1>
        <p>{{ $desc }}</p>
        <a href="{{ route('home') }}" class="btn">← Volver al inicio</a>
        <div class="tag">En construcción · Fase 2</div>
    </div>
</body>
</html>

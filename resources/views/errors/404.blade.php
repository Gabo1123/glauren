@php
    use App\Modules\Chaquetas\Models\Setting;
    $brand = Setting::get('brand_name', 'Glauren');
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573183504386'));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Página no encontrada · {{ $brand }}</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--ink2:#141419;--panel:#1a1a20;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;overflow-x:hidden}
    .bg{position:fixed;inset:0;z-index:0;background:
        radial-gradient(620px 380px at 50% 0%,rgba(233,184,63,.16),transparent 62%),
        radial-gradient(480px 360px at 88% 108%,rgba(233,184,63,.07),transparent 60%);pointer-events:none}
    .card{position:relative;z-index:1;width:100%;max-width:560px;background:var(--ink2);border:1px solid rgba(244,239,227,.1);border-radius:22px;padding:46px 34px;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.5)}
    .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(233,184,63,.12);border:1px solid rgba(233,184,63,.4);color:var(--gold);font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.18em;text-transform:uppercase;padding:7px 14px;border-radius:999px;margin-bottom:22px}
    .num{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:clamp(110px,26vw,190px);line-height:.9;color:var(--gold);letter-spacing:-.01em;position:relative;display:inline-block;text-shadow:0 0 46px rgba(233,184,63,.35)}
    .num .zip{display:inline-block;position:relative;transform:translateY(4px)}
    .num .zip::after{content:"";position:absolute;left:50%;top:8%;bottom:8%;width:3px;transform:translateX(-50%);
        background:repeating-linear-gradient(#0d0d0f 0 4px,transparent 4px 8px)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(24px,5vw,38px);line-height:1;margin:6px 0 12px}
    h1 .g{color:var(--gold)}
    p.lead{color:var(--muted);font-size:16px;line-height:1.6;max-width:400px;margin:0 auto 30px}
    .btns{display:flex;gap:12px;flex-wrap:wrap;justify-content:center}
    .btn{display:inline-flex;align-items:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:16px;padding:14px 22px;border:2px solid #000;border-radius:10px;text-decoration:none;box-shadow:4px 4px 0 #000;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503} .btn-wa{background:var(--ok);color:#04240f}
    .btn-line{background:var(--panel);color:var(--paper);box-shadow:4px 4px 0 var(--gold)}
    .help{margin-top:26px;color:var(--muted);font-size:13px;font-family:'Space Mono',monospace}
    .help a{color:var(--gold);text-decoration:none}
    @media(max-width:480px){.card{padding:36px 20px}.btn{width:100%;justify-content:center}}
</style>
</head>
<body>
    <div class="bg"></div>
    <div class="card">
        <span class="badge">Error 404 · Página perdida</span>
        <div class="num">4<span class="zip">0</span>4</div>
        <h1>Esta prenda no está<br>en el <span class="g">perchero</span> 🧥</h1>
        <p class="lead">La página que buscas se descosió o cambió de lugar. Pero diseñar tu chaqueta de promoción sigue a un clic.</p>
        <div class="btns">
            <a href="{{ route('home') }}" class="btn btn-gold">← Volver al inicio</a>
            <a href="{{ route('personalizar') }}" class="btn btn-line">Diseña la tuya →</a>
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola '.$brand.', necesito ayuda 🙌') }}" target="_blank" rel="noopener" class="btn btn-wa">💬 Escríbenos</a>
        </div>
        <div class="help">¿Buscabas algo puntual? Escríbenos por WhatsApp <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">+{{ $wa }}</a></div>
    </div>
</body>
</html>

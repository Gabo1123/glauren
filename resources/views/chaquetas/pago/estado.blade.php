@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · Glauren</title>
<meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@800;900&family=Hanken+Grotesk:wght@400;500;600&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--paper:#f4efe3;--blast:#e9b83f;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;
        background-image:radial-gradient(900px 500px at 85% -10%,rgba(43,52,255,.16),transparent 60%)}
    .card{max-width:460px;width:100%;background:var(--panel);border:3px solid #000;border-radius:14px;padding:38px;text-align:center;
        box-shadow:10px 10px 0 {{ $ok ? 'var(--ok)' : 'var(--blast)' }}}
    .ic{font-size:52px;line-height:1;margin-bottom:14px}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:34px;line-height:.9;margin-bottom:12px}
    p{color:var(--muted);margin-bottom:24px}
    .btn{display:inline-block;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:13px 22px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;box-shadow:4px 4px 0 #000;margin:4px}
    .btn.wa{background:var(--ok);color:#04240f}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    a{text-decoration:none}
</style>
</head>
<body>
    <div class="card">
        <div class="ic">{{ $ok ? '✅' : '⏳' }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $msg }}</p>
        <div>
            <a href="{{ route('seguimiento') }}" class="btn">Ver mi seguimiento</a>
            @unless($ok)
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, tengo una duda con el pago de mi chaqueta.') }}" target="_blank" rel="noopener" class="btn wa">WhatsApp</a>
            @endunless
        </div>
    </div>
</body>
</html>

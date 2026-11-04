@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573021106592'));
    $phone = Setting::get('contact_phone', '302 110 6592');
    $sent = session('sent');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'empresas',
    'seoTitleOverride' => 'Chaquetas y dotación corporativa con tu logo · G\'Lauren',
    'seoDescriptionOverride' => 'Chaquetas, busos, chalecos y camisetas para empresas con tu logo bordado. Telas Lafayette, precios al por mayor, entrega en 25 a 35 días y 1 chaqueta de obsequio por cada 25 unidades.',
    'ogImageOverride' => url('/img/cotizacion/empresas/equipo-1.jpg'),
    'breadcrumbs' => [['name' => 'Inicio', 'url' => url('/')], ['name' => 'Empresas', 'url' => route('empresas')]],
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.6}
    a{text-decoration:none;color:inherit}
    .wrap{max-width:1140px;margin:0 auto;padding:0 24px}
    nav{display:flex;align-items:center;justify-content:space-between;padding:15px 24px;border-bottom:2px solid #000;background:rgba(13,13,15,.92);position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}
    .brand{display:flex;align-items:center;gap:11px}
    .brand .mk{width:40px;height:40px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:22px;letter-spacing:.04em}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:18px;padding:13px 24px;border:2px solid #000;border-radius:10px;box-shadow:5px 5px 0 #000;cursor:pointer;transition:transform .12s,box-shadow .12s}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503}.btn-wa{background:var(--ok);color:#04240f}.btn-line{background:var(--ink);color:var(--paper);box-shadow:5px 5px 0 var(--gold)}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--gold)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(44px,7vw,86px);line-height:.86;margin:12px 0 18px}
    h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(32px,5vw,54px);line-height:.92;margin:8px 0 26px}
    .gold{color:var(--gold)}
    header.hero{padding:56px 0 46px;border-bottom:2px solid #000;background:radial-gradient(800px 420px at 90% -10%,rgba(233,184,63,.18),transparent 60%)}
    .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center}
    .lead{font-size:19px;color:#cfc9bd;margin-bottom:24px;max-width:560px}
    .cta-row{display:flex;gap:12px;flex-wrap:wrap}
    .hero-pic{position:relative}
    .hero-pic img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:18px;border:3px solid #000;box-shadow:12px 12px 0 var(--gold);display:block}
    .seal{position:absolute;left:-18px;bottom:-18px;background:#ff6b1a;color:#0e0e0e;border:3px solid #000;border-radius:50%;width:112px;height:112px;display:flex;flex-direction:column;align-items:center;justify-content:center;transform:rotate(-8deg);font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:34px;line-height:.9;text-align:center}
    .seal small{font-family:'Hanken Grotesk',sans-serif;font-size:9px;font-weight:800;line-height:1.15;max-width:80px;margin-top:3px}
    section{padding:60px 0}
    .feats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .feat{background:var(--panel);border:2px solid #000;border-radius:14px;padding:20px;box-shadow:6px 6px 0 #000}
    .feat .em{font-size:30px}
    .feat h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:21px;margin:6px 0 4px}
    .feat p{color:var(--muted);font-size:14px}
    .gal{display:grid;grid-template-columns:2fr 1fr;grid-template-rows:220px 220px;gap:14px}
    .gal img{width:100%;height:100%;object-fit:cover;border-radius:14px;border:3px solid #000;display:block}
    .gal .big{grid-row:span 2}
    .gal .white{background:#fff;object-fit:contain}
    .steps{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;counter-reset:s}
    .step{background:var(--panel);border:2px solid #000;border-radius:14px;padding:16px;box-shadow:5px 5px 0 var(--gold)}
    .step:before{counter-increment:s;content:counter(s);display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:var(--gold);color:#1c1503;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:18px;margin-bottom:8px;border:2px solid #000}
    .step b{display:block;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:18px}
    .step span{color:var(--muted);font-size:13px}
    .gift{background:var(--gold);color:#1c1503;border:3px solid #000;border-radius:16px;padding:22px 26px;box-shadow:8px 8px 0 #000;display:flex;gap:20px;align-items:center;flex-wrap:wrap}
    .gift .big{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:64px;line-height:.9}
    .gift b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:26px;line-height:1}
    .form-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:40px;align-items:start}
    form.card{background:var(--panel);border:2px solid #000;border-radius:16px;padding:24px;box-shadow:8px 8px 0 var(--gold);display:grid;grid-template-columns:1fr 1fr;gap:12px}
    form.card .full{grid-column:1/-1}
    label{display:flex;flex-direction:column;gap:5px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
    label small{text-transform:none;letter-spacing:0;font-weight:500}
    input,select,textarea{background:#0f0f13;border:2px solid #000;border-radius:10px;color:var(--paper);padding:11px 12px;font-size:16px;font-family:inherit;font-weight:500;text-transform:none;letter-spacing:0}
    input:focus,select:focus,textarea:focus{outline:none;border-color:var(--gold)}
    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chips label{flex-direction:row;align-items:center;gap:7px;background:#0f0f13;border:2px solid #000;border-radius:999px;padding:8px 13px;font-size:13px;text-transform:none;letter-spacing:0;color:var(--paper);cursor:pointer}
    .chips input{width:16px;height:16px;accent-color:var(--gold);padding:0}
    .ck{flex-direction:row!important;align-items:flex-start;gap:9px!important;text-transform:none!important;letter-spacing:0!important;font-weight:500!important;font-size:13px!important}
    .ck input{width:18px;height:18px;margin-top:2px;accent-color:var(--gold)}
    .err{background:rgba(255,74,61,.12);border:1.5px solid #ff4a3d;color:#ffb0a8;border-radius:10px;padding:10px 12px;font-size:14px}
    .ok{background:rgba(37,211,102,.12);border:2px solid var(--ok);color:#c9f7da;border-radius:14px;padding:22px;text-align:center}
    .ok b{display:block;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:30px;color:var(--ok);margin-bottom:6px}
    .hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
    footer{border-top:2px solid #000;padding:26px 0;color:var(--muted);font-size:13px;text-align:center}
    @media(max-width:900px){.hero-grid,.form-grid{grid-template-columns:1fr}.feats{grid-template-columns:1fr 1fr}.steps{grid-template-columns:1fr 1fr}.gal{grid-template-columns:1fr;grid-template-rows:auto}.gal img{height:220px}.gal .big{grid-row:auto}}
    @media(max-width:560px){.feats,.steps{grid-template-columns:1fr}form.card{grid-template-columns:1fr}.seal{width:88px;height:88px;font-size:26px;left:-8px}}
</style>
</head>
<body>
<nav>
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">Glauren</span></a>
    <a href="#cotizar" class="btn btn-gold" style="font-size:15px;padding:10px 18px">Cotizar para mi empresa</a>
</nav>

<header class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="ey">// Empresas y dotación</span>
            <h1>Tu equipo, <span class="gold">con tu marca</span></h1>
            <p class="lead">Chaquetas, busos, chalecos y camisetas con el logo de tu empresa bordado o estampado. Calidad de prom, precios al por mayor.</p>
            <div class="cta-row">
                <a href="#cotizar" class="btn btn-gold">Pedir cotización →</a>
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola G\'Lauren, quiero cotizar dotación / chaquetas para mi empresa.') }}" target="_blank" rel="noopener" class="btn btn-wa">WhatsApp</a>
            </div>
        </div>
        <div class="hero-pic">
            <img src="/img/cotizacion/empresas/equipo-1.jpg" alt="Equipo de trabajo con chaquetas corporativas bordadas">
            <div class="seal">1×25<small>CHAQUETA GRATIS</small></div>
        </div>
    </div>
</header>

<section>
    <div class="wrap">
        <span class="ey">// Por qué G'Lauren</span>
        <h2>Prendas que <span class="gold">representan</span> tu empresa</h2>
        <div class="feats">
            <div class="feat"><div class="em">🧵</div><h3>Tu logo bordado</h3><p>Bordado o estampado con los colores exactos de tu marca, en el pecho, la espalda o la manga.</p></div>
            <div class="feat"><div class="em">🧥</div><h3>Telas premium</h3><p>Lafayette y Patprimo, cremalleras YES. Prendas hechas para el uso diario.</p></div>
            <div class="feat"><div class="em">💲</div><h3>Precios al por mayor</h3><p>Cotización por unidad según cantidades, con cada prenda en foto y sus características.</p></div>
            <div class="feat"><div class="em">🚚</div><h3>25 a 35 días</h3><p>Días hábiles desde la aprobación del diseño y el anticipo.</p></div>
            <div class="feat"><div class="em">🛡️</div><h3>Garantía</h3><p>Por defectos de fabricación: confección y cremalleras.</p></div>
            <div class="feat"><div class="em">💳</div><h3>Pago 50 / 50</h3><p>50% para iniciar y 50% contra entrega.</p></div>
        </div>
    </div>
</section>

<section style="padding-top:10px">
    <div class="wrap">
        <div class="gal">
            <img class="big" src="/img/cotizacion/empresas/equipo-2.jpg" alt="Equipo con chaquetas corporativas" loading="lazy">
            <img src="/img/cotizacion/empresas/equipo-1.jpg" alt="Chaquetas corporativas azules" loading="lazy" style="object-position:50% 30%">
            <img class="white" src="/img/cotizacion/empresas/chaqueta-1.jpg" alt="Chaqueta corporativa con reflectivos" loading="lazy">
        </div>
    </div>
</section>

<section style="padding-top:20px">
    <div class="wrap">
        <div class="gift">
            <div class="big">1×25</div>
            <div style="flex:1;min-width:240px"><b>1 chaqueta adicional de obsequio por cada 25 unidades</b><div style="font-weight:600;margin-top:6px">+ tula de obsequio con tu pedido.</div></div>
            <a href="#cotizar" class="btn btn-line">Quiero mi cotización</a>
        </div>
    </div>
</section>

<section>
    <div class="wrap">
        <span class="ey">// Cómo trabajamos</span>
        <h2>Del sí a la <span class="gold">entrega</span></h2>
        <div class="steps">
            <div class="step"><b>Cotización</b><span>Te enviamos cada prenda con foto, características y valor por unidad.</span></div>
            <div class="step"><b>Anticipo 50%</b><span>Con el anticipo separamos telas y arrancamos.</span></div>
            <div class="step"><b>Diseño y tallas</b><span>Diseño con tu logo y tabla de tallas del equipo.</span></div>
            <div class="step"><b>Confección</b><span>Corte, bordado o estampado y control de calidad.</span></div>
            <div class="step"><b>Entrega</b><span>Empacado por persona; se paga el saldo.</span></div>
        </div>
    </div>
</section>

<section id="cotizar" style="scroll-margin-top:80px">
    <div class="wrap form-grid">
        <div>
            <span class="ey">// Cotiza sin compromiso</span>
            <h2>Pide tu <span class="gold">cotización</span></h2>
            <p class="lead" style="font-size:17px">Cuéntanos qué necesitas y te enviamos la cotización con fotos, características y precios al por mayor lo antes posible.</p>
            <p style="color:var(--muted);font-size:14px">¿Prefieres hablar? Escríbenos al <a href="https://wa.me/{{ $wa }}" class="gold" target="_blank" rel="noopener">{{ $phone }}</a>.</p>
        </div>

        @if($sent)
            <div class="ok">
                <b>¡Recibimos tu solicitud!</b>
                Tu número de cotización es <strong>{{ $sent }}</strong>. Un asesor la prepara y te la envía por WhatsApp o correo muy pronto.
                <div style="margin-top:14px"><a href="{{ route('home') }}" class="btn btn-line" style="font-size:15px">Volver al inicio</a></div>
            </div>
        @else
        <form method="POST" action="{{ route('empresas.store') }}" enctype="multipart/form-data" class="card">
            @csrf
            <div class="hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            @if($errors->any())<div class="err full">⚠ {{ $errors->first() }}</div>@endif
            <label class="full">Empresa<input name="company" value="{{ old('company') }}" required maxlength="160" placeholder="Ej.: Constructora Andes S.A.S."></label>
            <label>NIT <small>(opcional)</small><input name="nit" value="{{ old('nit') }}" maxlength="40"></label>
            <label>Ciudad<input name="city" value="{{ old('city') }}" maxlength="120" placeholder="Bogotá"></label>
            <label>Tu nombre<input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"></label>
            <label>Cargo <small>(opcional)</small><input name="role" value="{{ old('role') }}" maxlength="120" placeholder="Compras, RR. HH.…"></label>
            <label>Celular / WhatsApp<input name="phone" value="{{ old('phone') }}" required maxlength="20" inputmode="tel" autocomplete="tel"></label>
            <label>Correo <small>(opcional)</small><input type="email" name="email" value="{{ old('email') }}" maxlength="160" autocomplete="email"></label>
            <div class="full"><label style="margin-bottom:6px">¿Qué prendas necesitas?</label>
                <div class="chips">
                    @foreach($garments as $g)
                        <label><input type="checkbox" name="garments[]" value="{{ $g }}" {{ in_array($g, old('garments', ['Chaquetas']), true) ? 'checked' : '' }}> {{ $g }}</label>
                    @endforeach
                </div>
            </div>
            <label>Unidades aprox.<input type="number" name="units" value="{{ old('units', 25) }}" min="1" max="100000" required></label>
            <label>Tu logo <small>(opcional)</small><input type="file" name="logo" accept="image/*,.pdf,.svg" style="padding:8px"></label>
            <label class="full">Cuéntanos más <small>(colores, uso, fecha que necesitas…)</small><textarea name="message" rows="3" maxlength="2000">{{ old('message') }}</textarea></label>
            <label class="ck full"><input type="checkbox" name="accept" value="1" {{ old('accept', '1') ? 'checked' : '' }} required> Acepto el tratamiento de mis datos para recibir la cotización.</label>
            <button class="btn btn-gold full">Enviar solicitud →</button>
        </form>
        @endif
    </div>
</section>

<footer>
    <div class="wrap">G'LAUREN · NIT 1033779133-6 · Calle 48K 5A-27 sur, Bogotá · {{ $phone }} · <a href="{{ route('home') }}" class="gold">chaquetasdeprom.com</a></div>
</footer>
@if($errors->any() || $sent)
<script>document.getElementById('cotizar').scrollIntoView();</script>
@endif
</body>
</html>

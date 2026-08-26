@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
    $phone = Setting::get('contact_phone', '3246750207');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'colegios',
    'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>'Colegios','url'=>route('colegios')]],
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--ink2:#151519;--panel:#1a1a20;--paper:#f4efe3;--gold:#e9b83f;--gold2:#f2cf63;--hot:#ff4a3d;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.6}
    a{text-decoration:none;color:inherit}
    .wrap{max-width:1140px;margin:0 auto;padding:0 24px}
    nav{display:flex;align-items:center;justify-content:space-between;padding:15px 24px;border-bottom:2px solid #000;background:rgba(13,13,15,.9);position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}
    .brand{display:flex;align-items:center;gap:11px}
    .brand .mk{width:40px;height:40px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:22px;letter-spacing:.04em}
    .btn{display:inline-flex;align-items:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.03em;font-size:17px;padding:12px 22px;border:2px solid #000;border-radius:8px;cursor:pointer;box-shadow:4px 4px 0 #000;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503} .btn-wa{background:var(--ok);color:#04240f}
    .btn-line{background:var(--ink);color:var(--paper);box-shadow:4px 4px 0 var(--gold)}
    /* Botón premium destacado para "Entrar al portal" */
    .btn-portal{position:relative;overflow:hidden;background:linear-gradient(120deg,#f6cf5b 0%,#e9b83f 45%,#c8941f 100%);color:#1c1503;font-size:19px;padding:15px 30px;box-shadow:0 0 0 2px #000,6px 6px 0 #000,0 0 26px rgba(233,184,63,.55);animation:portalGlow 2.4s ease-in-out infinite}
    .btn-portal:hover{transform:translate(2px,2px);box-shadow:0 0 0 2px #000,3px 3px 0 #000,0 0 34px rgba(233,184,63,.8)}
    .btn-portal .arw{transition:transform .2s} .btn-portal:hover .arw{transform:translateX(4px)}
    .btn-portal::after{content:"";position:absolute;top:0;left:-120%;width:60%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.65),transparent);transform:skewX(-20deg);animation:portalShine 3.2s ease-in-out infinite}
    @keyframes portalGlow{0%,100%{box-shadow:0 0 0 2px #000,6px 6px 0 #000,0 0 20px rgba(233,184,63,.45)}50%{box-shadow:0 0 0 2px #000,6px 6px 0 #000,0 0 32px rgba(233,184,63,.85)}}
    @keyframes portalShine{0%{left:-120%}55%,100%{left:130%}}
    @media(prefers-reduced-motion:reduce){.btn-portal{animation:none}.btn-portal::after{display:none}}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--gold)}
    /* hero */
    header.hero{padding:64px 0 40px;background:radial-gradient(800px 400px at 85% -10%,rgba(233,184,63,.16),transparent 60%);border-bottom:2px solid #000}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(42px,7.5vw,88px);line-height:.85;margin:12px 0 16px}
    h1 .gold{color:var(--gold)}
    .lead{font-size:19px;color:#cfc9bd;max-width:600px;margin-bottom:26px}
    .cta-row{display:flex;gap:12px;flex-wrap:wrap}
    /* free banner */
    .free{margin:26px 0 0;background:var(--gold);color:#1c1503;border:3px solid #000;border-radius:12px;padding:18px 22px;box-shadow:8px 8px 0 #000;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
    .free .em{font-size:30px}
    .free b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:22px}
    /* secciones */
    section{padding:56px 0}
    .head h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(30px,5vw,54px);line-height:.9;margin:8px 0 30px}
    .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
    .feat{border:2px solid #000;background:var(--panel);border-radius:12px;padding:22px;box-shadow:6px 6px 0 #000;display:flex;gap:14px}
    .feat .em{font-size:30px;line-height:1}
    .feat h3{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:20px;margin-bottom:4px}
    .feat p{color:var(--muted);font-size:14px}
    @media(max-width:720px){.grid{grid-template-columns:1fr}}
    /* pasos */
    .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .step{border:2px solid #000;background:var(--panel);border-radius:10px;padding:20px;box-shadow:6px 6px 0 var(--gold)}
    .step .no{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:38px;color:var(--gold);line-height:.8;-webkit-text-stroke:1px #000}
    .step h4{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:17px;margin:8px 0 4px}
    .step p{color:var(--muted);font-size:13px}
    @media(max-width:720px){.steps{grid-template-columns:1fr 1fr}}
    /* Mockups del portal */
    .show{display:grid;grid-template-columns:1.15fr .85fr;gap:26px;align-items:center}
    @media(max-width:820px){.show{grid-template-columns:1fr}}
    .win{background:#0f0f13;border:1px solid var(--line);border-radius:14px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.5)}
    .win-bar{display:flex;align-items:center;gap:6px;padding:11px 14px;background:#17171d;border-bottom:1px solid var(--line);font-size:12px;color:var(--muted)}
    .win-bar span{width:10px;height:10px;border-radius:50%;background:#3a3a44}.win-bar b{margin-left:8px;color:#e7e3d8}
    .win-body{padding:20px;display:flex;gap:18px;align-items:center;flex-wrap:wrap}
    .win-stat{text-align:center;background:rgba(233,184,63,.1);border:1px solid rgba(233,184,63,.35);border-radius:12px;padding:16px 20px}
    .win-stat .n{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:38px;color:var(--gold);line-height:1}
    .win-stat .l{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .win-rows{flex:1;min-width:180px;display:flex;flex-direction:column;gap:8px}
    .win-row{display:flex;justify-content:space-between;align-items:center;background:#16161c;border:1px solid var(--line);border-radius:9px;padding:11px 13px;font-size:13.5px;color:#e7e3d8}
    .win-row i{font-style:normal;font-size:11px;color:var(--gold);background:rgba(233,184,63,.12);padding:3px 9px;border-radius:999px}
    .phone{width:210px;margin:0 auto;background:#0f0f13;border:6px solid #17171d;border-radius:28px;padding:14px;box-shadow:0 18px 50px rgba(0,0,0,.55)}
    .phone .pt{text-align:center;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-weight:800;color:var(--gold);font-size:15px;margin-bottom:12px}
    .phone .pp{display:flex;align-items:center;gap:9px;margin-bottom:12px}
    .phone .pav{width:38px;height:38px;border-radius:50%;background:rgba(233,184,63,.16);border:1px solid var(--gold);display:grid;place-items:center;font-size:18px}
    .phone .pp b{display:block;font-size:13px}.phone .pp span{color:var(--muted);font-size:11px}
    .phone .pbar{height:7px;background:#26262e;border-radius:999px;overflow:hidden;margin-bottom:10px}.phone .pbar i{display:block;height:100%;width:80%;background:var(--gold)}
    .phone .pf{display:flex;flex-direction:column;gap:7px}
    .phone .pf div{background:#16161c;border:1px solid var(--line);border-radius:8px;padding:8px 10px;font-size:12px;color:#cfc9bd}
    /* Números / confianza */
    .nums{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:8px}
    @media(max-width:720px){.nums{grid-template-columns:1fr 1fr}}
    .numc{text-align:center;border:2px solid #000;background:var(--panel);border-radius:12px;padding:22px 12px;box-shadow:6px 6px 0 #000}
    .numc .n{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:clamp(30px,5vw,44px);color:var(--gold);line-height:1}
    .numc .l{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px}
    /* cta final */
    .band{border-top:3px solid var(--gold);border-bottom:3px solid var(--gold);background:radial-gradient(700px 320px at 50% -20%,rgba(233,184,63,.16),transparent 60%),#0d0d0f;text-align:center;padding:56px 24px}
    .band h2{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,5.5vw,58px);color:var(--gold);margin-bottom:12px}
    .band p{color:#cfc9bd;margin-bottom:24px}
    footer{padding:30px 0;text-align:center;color:var(--muted);font-family:'Space Mono',monospace;font-size:12px}
</style>
</head>
<body>
<nav>
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">Glauren</span></a>
    <a href="{{ route('portal.login') }}" class="btn btn-portal" style="font-size:15px;padding:10px 20px">🔐 Entrar al portal <span class="arw">→</span></a>
</nav>

<header class="hero">
    <div class="wrap">
        <span class="ey">// Para colegios y promociones</span>
        <h1>El portal de<br><span class="gold">tu colegio</span></h1>
        <p class="lead">Contrato y firma en línea, avance de fabricación con fotos reales, y cada alumno verifica su pedido con su documento. Todo en un solo lugar, sin enredos.</p>
        <div class="cta-row">
            <a href="{{ route('portal.login') }}" class="btn btn-portal">🔐 Entrar al portal <span class="arw">→</span></a>
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, soy de un colegio y quiero cotizar las chaquetas de la promoción.') }}" target="_blank" rel="noopener" class="btn btn-wa">Cotizar por WhatsApp</a>
            <a href="{{ route('personalizar') }}" class="btn btn-line">Ver diseños</a>
        </div>
        <div class="free">
            <span class="em">🎁</span>
            <div><b>¡Tu chaqueta gratis!</b> Sé el primero de tu salón en llamarnos: si contratamos con tu colegio, tu prenda es <b>gratis para ti</b>.</div>
        </div>
    </div>
</header>

{{-- Números / confianza --}}
<section style="padding:36px 0 10px">
    <div class="wrap">
        <div class="nums">
            <div class="numc"><div class="n">+10</div><div class="l">Años de experiencia</div></div>
            <div class="numc"><div class="n">+80</div><div class="l">Colegios felices</div></div>
            <div class="numc"><div class="n">+500</div><div class="l">Promociones</div></div>
            <div class="numc"><div class="n">12</div><div class="l">Ciudades en Colombia</div></div>
        </div>
    </div>
</section>

<section style="padding-bottom:20px">
    <div class="wrap">
        <div class="head"><span class="ey">// No vendemos una prenda, vendemos una experiencia</span><h2>Así se ve<br>tu portal</h2></div>
        <div class="show">
            {{-- Panel del colegio --}}
            <div class="win">
                <div class="win-bar"><span></span><span></span><span></span><b>Portal del colegio · Glauren</b></div>
                <div class="win-body">
                    <div class="win-stat"><div class="n">80%</div><div class="l">Producción</div></div>
                    <div class="win-rows">
                        <div class="win-row"><span>✅ Contrato firmado</span></div>
                        <div class="win-row"><span>🧵 En producción</span><i>fotos</i></div>
                        <div class="win-row"><span>💰 Pagos 32 / 40</span></div>
                        <div class="win-row"><span>👥 40 estudiantes</span><i>Excel</i></div>
                    </div>
                </div>
            </div>
            {{-- Celular del estudiante --}}
            <div class="phone">
                <div class="pt">Mi chaqueta</div>
                <div class="pp"><div class="pav">🙂</div><div><b>Valentina</b><span>11B · San José</span></div></div>
                <div class="pbar"><i></i></div>
                <div class="pf">
                    <div>🧵 En producción · 80%</div>
                    <div>📸 Vale subió una foto</div>
                    <div>💬 “¡Ya casi chicos! 🔥”</div>
                    <div>🎓 Curso 11B · 38 miembros</div>
                </div>
            </div>
        </div>
        <p class="lead" style="text-align:center;max-width:640px;margin:26px auto 0">Tu colegio no solo recibe chaquetas: vive <b>toda la experiencia</b> — contrato, producción con fotos, y cada estudiante con su perfil. Más que una prenda, <b style="color:var(--gold)">un recuerdo</b>.</p>
    </div>
</section>

<section style="padding-top:24px">
    <div class="wrap">
        <div class="head"><span class="ey">// Qué incluye</span><h2>Tu cuenta,<br>todo controlado</h2></div>
        <div class="grid">
            <div class="feat"><span class="em">✍️</span><div><h3>Contrato y firma en línea</h3><p>Te enviamos el contrato de la promoción y lo firmas desde el celular, con validez y fecha registrada.</p></div></div>
            <div class="feat"><span class="em">📸</span><div><h3>Avance con fotos reales</h3><p>Ves en qué etapa va la producción y las fotos del proceso, sin tener que llamar a preguntar.</p></div></div>
            <div class="feat"><span class="em">🪪</span><div><h3>Alumnos por documento</h3><p>Cada estudiante entra con su tarjeta de identidad y ve su chaqueta, sus medidas y su pago.</p></div></div>
            <div class="feat"><span class="em">🧾</span><div><h3>Todo organizado</h3><p>Lista de alumnos, tallas y pagos del curso en un panel claro. Puedes cargarlos por Excel.</p></div></div>
        </div>
    </div>
</section>

<section style="background:var(--ink2);border-top:2px solid #000;border-bottom:2px solid #000">
    <div class="wrap">
        <div class="head"><span class="ey">// Cómo funciona</span><h2>De la idea<br>a la entrega</h2></div>
        <div class="steps">
            <div class="step"><div class="no">01</div><h4>Nos contactas</h4><p>Escríbenos y elegimos el modelo y diseño de tu promoción.</p></div>
            <div class="step"><div class="no">02</div><h4>Firmas el contrato</h4><p>Lo revisas y firmas en línea; te damos tu cuenta del portal.</p></div>
            <div class="step"><div class="no">03</div><h4>Fabricamos</h4><p>Tomamos medidas y produces con seguimiento y fotos.</p></div>
            <div class="step"><div class="no">04</div><h4>Entregamos</h4><p>Cada alumno recibe su chaqueta y paga en línea o contigo.</p></div>
        </div>
    </div>
</section>

{{-- Beneficios / obsequios del combo --}}
<section>
    <div class="wrap">
        <div class="head"><span class="ey">// Más que chaquetas</span><h2>Beneficios<br>para tu promoción</h2></div>
        <div class="grid">
            <div class="feat"><span class="em">🎁</span><div><h3>Tu prenda GRATIS</h3><p>Si eres el <b>primero de tu salón</b> en llamarnos y hacemos la contratación con tu colegio, ¡tu chaqueta o buso es <b>gratis para ti</b>!</p></div></div>
            <div class="feat"><span class="em">👕</span><div><h3>Cortesía por cantidad</h3><p>Por cada cierto número de estudiantes, una unidad adicional de cortesía (según el combo).</p></div></div>
            <div class="feat"><span class="em">🎒</span><div><h3>Extras del combo</h3><p>Mochila de cuerdas, globos para la entrega y prenda de obsequio para el tutor de once.</p></div></div>
            <div class="feat"><span class="em">📷</span><div><h3>Fotografía profesional</h3><p>Para grupos grandes en Bogotá y Cundinamarca, sesión de fotos incluida según el combo.</p></div></div>
        </div>
        <p class="lead" style="text-align:center;max-width:620px;margin:22px auto 0;font-size:15px">*Los beneficios dependen del combo, la cantidad y la ciudad. Los confirmamos en tu cotización.</p>
    </div>
</section>

{{-- Por qué Glauren --}}
<section style="background:var(--ink2);border-top:2px solid #000;border-bottom:2px solid #000">
    <div class="wrap">
        <div class="head"><span class="ey">// Por qué elegirnos</span><h2>Calidad que<br>se nota</h2></div>
        <div class="grid">
            <div class="feat"><span class="em">🧵</span><div><h3>Telas de alta calidad</h3><p>Lafayette y Patprimo según la referencia. Bordados premium que duran.</p></div></div>
            <div class="feat"><span class="em">🛡️</span><div><h3>Garantía 1 año</h3><p>Cubrimos defectos de fabricación durante todo el año escolar.</p></div></div>
            <div class="feat"><span class="em">🎨</span><div><h3>Diseño 3D propio</h3><p>Diseña y visualiza la chaqueta de tu curso en 3D antes de fabricarla.</p></div></div>
            <div class="feat"><span class="em">🤝</span><div><h3>+10 años de experiencia</h3><p>Miles de promociones felices en toda Colombia. Sabemos lo que hacemos.</p></div></div>
        </div>
    </div>
</section>

<div class="band">
    <div class="wrap">
        <span class="ey">// Empieza hoy</span>
        <h2>Sé el primero<br>de tu colegio</h2>
        <p>Contrata la promoción y llévate tu chaqueta gratis. Te asesoramos en todo el proceso.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, quiero contratar las chaquetas de mi colegio.') }}" target="_blank" rel="noopener" class="btn btn-wa">Escríbenos por WhatsApp</a>
            <a href="{{ route('portal.login') }}" class="btn btn-portal">🔐 Entrar al portal <span class="arw">→</span></a>
        </div>
    </div>
</div>

<footer>
    <a href="{{ route('home') }}">← Volver al inicio</a> · Tel {{ $phone }} · Glauren © {{ date('Y') }}
</footer>
</body>
</html>

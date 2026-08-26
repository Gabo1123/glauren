@php
    use App\Modules\Chaquetas\Models\Setting;
    use App\Modules\Chaquetas\Models\MenuItem;
    use App\Modules\Chaquetas\Models\Page;
    use App\Modules\Chaquetas\Models\ShowcasePhoto;
    $nav = MenuItem::tree();
    $cityPages = Page::where('slug', 'like', 'chaquetas-de-prom-en-%')->where('published', true)->orderBy('sort')->get();
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
    $phone = Setting::get('contact_phone', '3246750207');
    $heroImg = Setting::get('home_hero_image', 'https://sp-ao.shortpixel.ai/client/to_auto,q_glossy,ret_img,w_1600/https://chaquetasdeprom.com/wp-content/uploads/2023/09/Glauren-la-mejor-marca-de-chaquetas-de-prom-en-colombia-medellin-bogota-santamarta.jpg');
    // Galería "Ya visten Glauren" — administrable desde Admin → Promociones
    $gallery = ShowcasePhoto::where('active', true)->orderBy('sort')->get()
        ->map(fn ($p) => [$p->imageUrl(), $p->label])->all();
    if (empty($gallery)) {
        $sp = 'https://sp-ao.shortpixel.ai/client/to_auto,q_glossy,ret_img,';
        $gallery = [
            [$sp.'w_1024,h_461/https://chaquetasdeprom.com/wp-content/uploads/2025/03/Colegio-universidad-catolica-1.jpg', 'Universidad Católica'],
            [$sp.'w_1024,h_461/https://chaquetasdeprom.com/wp-content/uploads/2025/03/Colegio-La-Giralda-Prom-2025-1.jpg', 'Colegio La Giralda'],
        ];
    }
    $testimonios = \App\Modules\Chaquetas\Models\Testimonial::where('active', true)->orderBy('sort')->get()
        ->map(fn ($t) => [$t->text, $t->name, $t->role, $t->photoUrl(), $t->rating])->all();
    if (empty($testimonios)) {
        $testimonios = [
            ['Me gustó mucho, mi curso quedó feliz. Hacen un trabajo brutal.', 'Mariana González', 'Estudiante de once', null, 5],
            ['Contraté con Glauren y me encantó que los muchachos entienden cada etapa del proceso.', 'Alberto Jiménez', 'Profesor', null, 5],
            ['Me entregaron la chaqueta y el buso rapidísimo, quedaron increíbles.', 'Willie Martínez', 'Estudiante 2024', null, 5],
        ];
    }
    // Íconos SVG propios (sin dependencias)
    $svgWa = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.1-1.7-.8-1.9-.9-.3-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.1.2-.3.2-.5.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.5-1.9-.2-.3 0-.4.1-.6l.4-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2.1-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 .9-1 2.3s1 2.7 1.2 2.9c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.7-.7 1.9-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3zM12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>';
    $svgIg = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4a3.9 3.9 0 01-1.4-.9 3.9 3.9 0 01-.9-1.4c-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 1018.6 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg>';
    $svgTk = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M16.6 5.8a4.3 4.3 0 01-2.5-3.8h-3v12.2a2.4 2.4 0 11-2.4-2.4c.2 0 .5 0 .7.1V8.8a5.6 5.6 0 00-.7 0 5.4 5.4 0 105.4 5.4V8.6a7.2 7.2 0 004.2 1.3V6.9a4.3 4.3 0 01-1.7-.1z"/></svg>';
    $svgFb = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0022 12z"/></svg>';
    $svgPhone = '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.5.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 013 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.5.1.4 0 .7-.2 1l-2.3 2.3z"/></svg>';

    // Secciones editables desde Admin → Textos/Home (con textos actuales como default)
    $parseRows = function ($text) {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', trim((string) $text)) as $l) {
            $l = trim($l);
            if ($l === '') { continue; }
            $out[] = array_map('trim', explode('|', $l));
        }
        return $out;
    };
    $garRows = $parseRows(Setting::get('home_gar')) ?: [
        ['🛡️', 'Garantía 1 año', 'Cubrimos defectos de fábrica todo el año escolar.'],
        ['⚡', 'Entrega récord', 'Procesos ágiles, sin demoras ni sustos.'],
        ['🏆', '+10 años', 'Miles de promociones felices en Colombia.'],
        ['✨', 'Diseños únicos', 'Bordados de alta calidad · telas Lafayette y Patprimo.'],
    ];
    $stepRows = $parseRows(Setting::get('home_steps')) ?: [
        ['🎨', 'Diseña en 3D', 'Elige el tipo, colores por zona y suma escudos y nombres sobre el modelo real.'],
        ['✍️', 'Aprueba', 'Tu curso revisa el diseño y firmas el contrato en línea.'],
        ['📸', 'Fabricamos', 'Sigue la producción con fotos reales, etapa por etapa, con tu documento.'],
        ['📦', 'Recíbela', 'Paga en línea seguro y luce tu chaqueta el día de tu promoción.'],
    ];
    $numRows = $parseRows(Setting::get('home_numbers')) ?: [
        ['500', 'Promociones felices'], ['80', 'Colegios'], ['10', 'Años de experiencia'], ['12', 'Ciudades en Colombia'],
    ];
    $trustedLabel = Setting::get('home_trusted_label') ?: 'Colegios que ya visten Glauren';
    $trustedList = array_values(array_filter(array_map('trim', explode(',', Setting::get('home_trusted') ?: 'Universidad Católica, Colegio La Giralda, George Williams, Montreal, Generación Enoc, Liceo Freire'))));
    $seoContent = Setting::get('home_seo_content');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', ['seoKey' => 'home', 'faqs' => $faqs])
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#e9b83f">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=Hanken+Grotesk:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
    :root{
        --ink:#0d0d0f; --ink-2:#141418; --panel:#1a1a20; --panel2:#232330; --paper:#f4efe3;
        --blast:#e9b83f; --blast-2:#f2cf63; --gold:#e9b83f; --hot:#ff4a3d;
        --ok:#25d366; --muted:#9a9aa7; --line:rgba(244,239,227,.14);
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.6;overflow-x:hidden}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block}
    .wrap{max-width:1200px;margin:0 auto;padding:0 24px}
    section{padding:84px 0}
    .display{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;line-height:.9;letter-spacing:.005em}
    .mono{font-family:'Space Mono',monospace}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.18em;font-size:12px;color:var(--blast-2);display:inline-block}
    .paper .ey{color:var(--blast)}

    /* Botones streetwear (borde + sombra dura) */
    .btn{display:inline-flex;align-items:center;gap:9px;padding:14px 24px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.03em;font-size:18px;border:2px solid #000;border-radius:3px;cursor:pointer;transition:transform .1s,box-shadow .1s;box-shadow:5px 5px 0 #000}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503}
    .btn-blast{background:#0d0d0f;color:var(--gold);border:2px solid var(--gold);box-shadow:5px 5px 0 #000}
    .btn-blast:hover{box-shadow:3px 3px 0 #000}
    .btn-wa{background:#25d366;color:#04240f;position:relative;animation:wabreathe 2.6s ease-in-out infinite}
    .btn-wa::after{content:'';position:absolute;inset:-2px;border-radius:5px;box-shadow:0 0 0 0 rgba(37,211,102,.6);animation:wapulse 1.9s ease-out infinite;pointer-events:none}
    .btn-wa:hover{animation:none}
    @keyframes wabreathe{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}
    @keyframes wapulse{0%{box-shadow:0 0 0 0 rgba(37,211,102,.6)}70%{box-shadow:0 0 0 15px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)}}
    @media(prefers-reduced-motion:reduce){.btn-wa{animation:none}.btn-wa::after{animation:none}}
    .btn-line{background:var(--ink);color:var(--paper);box-shadow:5px 5px 0 var(--blast)}
    .btn-line:hover{box-shadow:3px 3px 0 var(--blast)}

    /* Sticker (parche rotado) */
    .sticker{display:inline-flex;align-items:center;gap:7px;font-family:'Space Mono',monospace;font-weight:700;text-transform:uppercase;letter-spacing:.04em;font-size:12px;padding:7px 12px;border:2px solid #000;border-radius:4px;box-shadow:3px 3px 0 #000;white-space:nowrap}
    .st-gold{background:var(--gold);color:#1c1503}
    .st-hot{background:var(--hot);color:#fff}
    .st-blast{background:#0d0d0f;color:var(--gold);border-color:#000}
    .st-paper{background:var(--paper);color:#111}

    /* HEADER */
    #siteHead{position:fixed;top:0;left:0;right:0;z-index:60;transition:background .25s,border-color .25s}
    #siteHead:not(.scrolled){background:linear-gradient(180deg,rgba(8,8,10,.78),transparent)}
    #siteHead.scrolled{background:rgba(10,10,12,.96);border-bottom:2px solid #000}
    .util{background:#0d0d0f;border-bottom:2px solid var(--gold);transition:.28s;max-height:40px;overflow:hidden}
    #siteHead.scrolled .util{max-height:0;border-color:transparent}
    .util-inner{max-width:1200px;margin:0 auto;padding:0 24px;height:38px;display:flex;align-items:center;justify-content:space-between;font-family:'Space Mono',monospace;font-size:12px;color:#fff}
    .util a{color:#fff;display:inline-flex;align-items:center;gap:6px} .util a:hover{color:var(--gold)}
    .util .u-right{display:flex;gap:16px;align-items:center}
    .u-soc{display:flex;gap:11px}
    @media(max-width:760px){.util .u-hide{display:none}}
    .mainnav{max-width:1200px;margin:0 auto;padding:0 24px;height:72px;display:flex;align-items:center;justify-content:space-between}
    .brand{display:flex;align-items:center;gap:13px}
    .brand .mk{position:relative;width:46px;height:46px;border-radius:10px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:26px;line-height:1;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand img{filter:brightness(0) invert(1)}
    .foot-brand .fb-logo img{filter:brightness(0) invert(1)}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:27px;letter-spacing:.04em;line-height:.85;color:var(--paper);display:block}
    .brand .tg{font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.2em;color:var(--gold);text-transform:uppercase;display:block;margin-top:3px}
    .navlinks{display:flex;gap:2px;align-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:700;font-size:17px;text-transform:uppercase;letter-spacing:.04em}
    .navlinks a{position:relative;color:var(--paper);padding:8px 14px;transition:.14s}
    .navlinks a:not(.nav-cta):hover{color:var(--gold)}
    .navlinks a.active:not(.nav-cta){color:var(--gold)}
    .navlinks a.active:not(.nav-cta)::before{content:'';position:absolute;left:14px;right:14px;bottom:2px;height:3px;background:var(--gold)}
    .nav-cta{margin-left:6px;background:var(--gold);color:#1c1503 !important;padding:10px 18px;border:2px solid #000;border-radius:3px;box-shadow:3px 3px 0 #000;display:inline-flex;align-items:center;gap:7px;transition:transform .1s,box-shadow .1s}
    .nav-cta:hover{transform:translate(2px,2px);box-shadow:1px 1px 0 #000}
    .nav-sep{width:1px;height:24px;background:rgba(244,239,227,.18);margin:0 8px;flex:none}
    .nav-contact{border:2px solid var(--gold);color:var(--gold) !important;border-radius:6px}
    .nav-contact:hover{background:var(--gold);color:#1c1503 !important}
    .nav-contact.active{background:rgba(233,184,63,.14)}
    .nav-contact.active::before{display:none}
    /* Submenú desplegable */
    .navdd{position:relative}
    .navdd > .ddlabel{cursor:pointer;display:inline-flex;align-items:center;gap:5px;color:var(--paper);padding:8px 14px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:16px}
    .navdd:hover > .ddlabel{color:var(--gold)}
    .navdd .ddmenu{position:absolute;top:100%;left:0;background:var(--ink-2);border:2px solid #000;border-radius:8px;min-width:250px;padding:6px;display:none;flex-direction:column;box-shadow:6px 6px 0 #000;z-index:65}
    .navdd:hover .ddmenu{display:flex}
    .navdd .ddmenu a{padding:10px 12px;border-radius:6px;font-family:'Hanken Grotesk',sans-serif;font-weight:600;font-size:14px;text-transform:none;letter-spacing:0;color:var(--paper)}
    .navdd .ddmenu a:hover{background:rgba(233,184,63,.12);color:var(--gold)}
    .navtoggle{display:none;background:var(--gold);border:2px solid #000;color:#1c1503;width:44px;height:44px;border-radius:4px;font-size:22px;cursor:pointer;box-shadow:3px 3px 0 #000;line-height:1}
    @media(max-width:900px){
        .navlinks{position:fixed;top:0;right:0;bottom:0;width:min(82vw,320px);flex-direction:column;align-items:stretch;gap:4px;background:var(--ink-2);border-left:2px solid #000;padding:84px 20px 30px;transform:translateX(100%);transition:transform .3s;z-index:70;font-size:22px;overflow-y:auto}
        body.nav-open .navlinks{transform:translateX(0)}
        .navlinks a{padding:12px 14px}
        .nav-cta{margin-left:0;justify-content:center;margin-top:6px}
        .nav-sep{width:100%;height:1px;margin:8px 0}
        .nav-contact{justify-content:center;margin-top:2px}
        .navtoggle{display:grid;place-items:center;z-index:80}
        .navdd .ddmenu{position:static;display:flex;border:none;box-shadow:none;background:transparent;padding:0 0 0 12px;min-width:0}
        .navdd > .ddlabel{font-size:20px;padding:12px 14px 4px}
    }

    /* HERO */
    .hero-bg{position:relative;width:100%;min-height:92vh;background-size:cover;background-position:72% center;display:flex;align-items:flex-end;border-bottom:3px solid #000}
    .hero-grad{position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,8,10,.94) 0%,rgba(8,8,10,.72) 38%,rgba(8,8,10,.2) 66%,transparent 84%),linear-gradient(180deg,transparent 48%,rgba(8,8,10,.9))}
    @media(max-width:760px){.hero-grad{background:linear-gradient(180deg,rgba(8,8,10,.55) 0%,rgba(8,8,10,.35) 40%,rgba(8,8,10,.92))}}
    .hero-inner{position:relative;z-index:2;width:100%;max-width:1200px;margin:0 auto;padding:0 24px 58px}
    .hero-stickers{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
    .hero-stickers .sticker:nth-child(1){transform:rotate(-3deg)}
    .hero-stickers .sticker:nth-child(2){transform:rotate(2deg)}
    .hero-stickers .sticker:nth-child(3){transform:rotate(-1.5deg)}
    .hero-h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(40px,5.6vw,74px);line-height:.9;letter-spacing:-.01em;color:var(--paper);margin-bottom:18px;max-width:740px;text-shadow:0 3px 26px rgba(0,0,0,.55)}
    .hero-h1 .out{color:transparent;-webkit-text-stroke:2.5px var(--gold);text-stroke:2.5px var(--gold)}
    .hero-h1 .fill{color:var(--gold);display:block;font-size:.6em;line-height:.98;margin-top:10px;letter-spacing:0}
    .hero-sub{font-size:16.5px;color:#efece3;max-width:540px;margin-bottom:26px;line-height:1.55;text-shadow:0 2px 16px rgba(0,0,0,.85)}
    .cta-actions{display:flex;gap:14px;flex-wrap:wrap}
    @media(max-width:640px){.cta-actions .btn{flex:1;justify-content:center}}

    /* TICKER marquee (SELLO) */
    .ticker{background:var(--gold);border-top:3px solid #000;border-bottom:3px solid #000;overflow:hidden;white-space:nowrap;padding:12px 0}
    .ticker--blast{background:#0d0d0f;border-top-color:var(--gold);border-bottom-color:var(--gold)}
    .ticker .run{display:inline-block;animation:mq 26s linear infinite;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:26px;letter-spacing:.02em;color:#111}
    .ticker--blast .run{color:var(--gold)}
    .ticker .run span{margin:0 14px}
    .ticker .run .dot{color:#111}
    .ticker--blast .run .dot{color:var(--paper)}
    .ticker:hover .run{animation-play-state:paused}
    @keyframes mq{from{transform:translateX(0)}to{transform:translateX(-50%)}}

    /* Headers de sección */
    .head{max-width:680px;margin-bottom:42px}
    .head h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(34px,5.5vw,68px);line-height:.9;margin:10px 0}
    .head p{color:var(--muted)}

    /* GARANTIAS (chips) */
    .gar-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
    .gar{border:2px solid #000;background:linear-gradient(180deg,var(--panel),#141418);border-radius:12px;padding:22px 20px;box-shadow:6px 6px 0 #000;display:flex;gap:15px;align-items:flex-start;transition:transform .14s,box-shadow .14s;position:relative;overflow:hidden}
    .gar::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--gold)}
    .gar:hover{transform:translate(-2px,-3px);box-shadow:9px 9px 0 var(--gold)}
    .gar .em{width:50px;height:50px;flex-shrink:0;border-radius:13px;background:rgba(233,184,63,.13);border:1.5px solid var(--gold);display:grid;place-items:center;font-size:24px;line-height:1}
    .gar h4{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;margin-bottom:3px;color:var(--paper)}
    .gar p{color:var(--muted);font-size:13px;line-height:1.45}
    @media(max-width:820px){.gar-grid{grid-template-columns:1fr 1fr}}
    @media(max-width:520px){.gar-grid{grid-template-columns:1fr}}

    /* PROCESO */
    .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;position:relative}
    .step{border:2px solid #000;background:linear-gradient(180deg,var(--panel),#141418);border-radius:12px;padding:24px 22px;box-shadow:6px 6px 0 #000;position:relative;transition:transform .14s,box-shadow .14s}
    .step:hover{transform:translate(-2px,-3px);box-shadow:9px 9px 0 var(--gold)}
    .step .s-top{display:flex;align-items:center;gap:12px;margin-bottom:14px}
    .step .no{width:46px;height:46px;flex-shrink:0;border-radius:50%;background:var(--gold);color:#1c1503;border:2px solid #000;box-shadow:3px 3px 0 #000;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;line-height:1}
    .step .ic{font-size:30px;line-height:1;margin-left:auto}
    .step h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:22px;margin-bottom:6px;color:var(--paper)}
    .step p{font-size:14px;color:var(--muted);line-height:1.5}
    /* Conector tipo flecha entre pasos (desktop) */
    .step:not(:last-child)::after{content:'→';position:absolute;top:34px;right:-15px;z-index:2;color:var(--gold);font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:24px;line-height:1}
    @media(max-width:820px){.steps{grid-template-columns:1fr 1fr}.step:not(:last-child)::after{display:none}}
    @media(max-width:480px){.steps{grid-template-columns:1fr}}

    /* Banda de ayuda / cotización -> WhatsApp */
    .helpband{position:relative;overflow:hidden;margin-top:26px;border:3px solid #000;border-radius:16px;background:linear-gradient(120deg,var(--gold) 0%,var(--blast-2) 55%,var(--gold) 100%);color:#1c1503;padding:28px 32px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;box-shadow:8px 8px 0 #000;transition:transform .25s cubic-bezier(.2,.8,.2,1),box-shadow .25s}
    .helpband:hover{transform:translate(-3px,-3px) rotate(-.4deg);box-shadow:13px 13px 0 #000}
    .helpband .hb-txt{max-width:620px;position:relative;z-index:2}
    .helpband h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(24px,3.2vw,38px);line-height:.92;margin:10px 0 6px}
    .helpband p{font-size:15px;color:#3a2e08;font-weight:500}
    .helpband .btn{position:relative;z-index:2}
    /* Brillo que barre la banda */
    .helpband::before{content:'';position:absolute;top:-50%;left:-75%;width:50%;height:200%;background:linear-gradient(115deg,transparent,rgba(255,255,255,.55),transparent);transform:skewX(-20deg);pointer-events:none;z-index:1}
    .helpband.in::before{animation:hbshine 2.6s ease-in-out .5s infinite}
    @keyframes hbshine{0%{left:-75%}55%,100%{left:135%}}
    /* Entrada premium */
    .helpband.reveal{opacity:0;transform:translateY(34px) scale(.94)}
    .helpband.reveal.in{opacity:1;transform:none;transition:opacity .75s cubic-bezier(.2,.8,.2,1),transform .75s cubic-bezier(.2,.8,.2,1)}
    .helpband.reveal .hb-txt > *{opacity:0;transform:translateY(14px)}
    .helpband.reveal.in .hb-txt > *{opacity:1;transform:none;transition:opacity .55s ease,transform .55s cubic-bezier(.2,.8,.2,1)}
    .helpband.reveal.in .hb-txt > *:nth-child(1){transition-delay:.18s}
    .helpband.reveal.in .hb-txt > *:nth-child(2){transition-delay:.30s}
    .helpband.reveal.in .hb-txt > *:nth-child(3){transition-delay:.42s}
    .helpband.reveal .btn{opacity:0}
    .helpband.reveal.in .btn{opacity:1;transition:opacity .6s ease .5s}
    .helpband .sticker{animation:hbsticker 3.2s ease-in-out infinite}
    @keyframes hbsticker{0%,100%{transform:rotate(-2deg) translateY(0)}50%{transform:rotate(-2deg) translateY(-4px)}}
    @media(max-width:640px){.helpband{padding:22px}.helpband .btn{width:100%;justify-content:center}}
    @media(prefers-reduced-motion:reduce){.helpband::before,.helpband .sticker,.helpband.reveal.in .btn{animation:none}.helpband.reveal,.helpband.reveal .hb-txt > *{opacity:1;transform:none}}

    /* GALERIA colegios */
    .gal-sec{background:var(--ink-2)}
    .promo-banner{display:block;margin-bottom:26px;border:3px solid #000;border-radius:16px;overflow:hidden;box-shadow:12px 12px 0 var(--gold);transition:transform .12s,box-shadow .12s}
    .promo-banner:hover{transform:translate(-2px,-2px);box-shadow:15px 15px 0 var(--gold)}
    .promo-banner img{width:100%;height:auto;display:block}

    /* NÚMEROS + prueba social */
    .numbers{background:radial-gradient(700px 300px at 50% -10%,rgba(233,184,63,.1),transparent 60%),var(--ink-2);border-top:2px solid #000;border-bottom:2px solid #000}
    .num-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;text-align:center}
    .num .nb{display:inline-flex;align-items:flex-start;justify-content:center}
    .num .n{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:clamp(42px,6.5vw,72px);color:var(--gold);line-height:1}
    .num .plus{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:clamp(26px,3.5vw,42px);color:var(--gold);margin-top:4px}
    .num .l{color:var(--muted);font-size:13px;margin-top:8px;text-transform:uppercase;letter-spacing:.06em;font-family:'Space Mono',monospace}
    @media(max-width:640px){.num-grid{grid-template-columns:1fr 1fr;gap:28px}}
    .trusted{margin-top:44px;text-align:center}
    .trusted .lbl{font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--gold);margin-bottom:14px}
    .trusted .logos{display:flex;flex-wrap:wrap;justify-content:center;gap:10px}
    .trusted .logos span{border:1.5px solid var(--line);border-radius:24px;padding:9px 17px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:15px;color:var(--paper);letter-spacing:.03em;transition:.15s}
    .trusted .logos span:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-2px)}
    .logos-toggle{display:none;margin-top:14px;background:transparent;border:1.5px solid var(--gold);color:var(--gold);border-radius:22px;padding:8px 18px;font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.05em;cursor:pointer}
    @media(max-width:640px){
        .trusted .logos.collapsed{max-height:118px;overflow:hidden;-webkit-mask-image:linear-gradient(#000 62%,transparent);mask-image:linear-gradient(#000 62%,transparent)}
        .trusted .logos.expanded{max-height:none;-webkit-mask-image:none;mask-image:none}
        .logos-toggle{display:inline-block}
    }
    /* Sección de video */
    .video-sec{padding:20px 0 10px}
    .video-frame{position:relative;max-width:900px;margin:0 auto;border:3px solid #000;border-radius:14px;overflow:hidden;box-shadow:8px 8px 0 var(--gold);background:#000;aspect-ratio:16/9}
    .video-frame.is-tk{max-width:340px;aspect-ratio:9/16}
    .video-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:block}
    .video-cta{display:flex;align-items:center;gap:16px;max-width:520px;margin:0 auto;padding:18px 22px;background:var(--panel);border:3px solid #000;border-radius:14px;box-shadow:6px 6px 0 var(--gold);text-decoration:none;color:var(--paper);transition:transform .12s,box-shadow .12s}
    .video-cta:hover{transform:translate(3px,3px);box-shadow:3px 3px 0 var(--gold)}
    .video-cta .vc-ic{display:grid;place-items:center;width:52px;height:52px;flex-shrink:0;background:var(--gold);color:#111;border:2px solid #000;border-radius:12px}
    .video-cta .vc-ic svg{width:26px;height:26px}
    .video-cta .vc-txt{flex:1;display:flex;flex-direction:column;line-height:1.2}
    .video-cta .vc-txt b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:20px}
    .video-cta .vc-txt small{color:var(--muted);font-size:13px}
    .video-cta .vc-go{font-family:'Space Mono',monospace;font-weight:700;color:var(--gold);white-space:nowrap}
    .video-follow{display:inline-flex;align-items:center;gap:8px;color:var(--gold);text-decoration:none;font-family:'Space Mono',monospace;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:.05em}
    .video-follow svg{width:16px;height:16px}
    .gallery{display:grid;grid-template-columns:repeat(4,1fr);grid-auto-rows:190px;gap:12px}
    .gallery a{position:relative;overflow:hidden;border:2px solid #000;border-radius:6px;box-shadow:4px 4px 0 #000}
    .gallery a img{width:100%;height:100%;object-fit:cover;transition:.4s}
    .gallery a:hover img{transform:scale(1.07)}
    .gallery a span{position:absolute;left:10px;bottom:10px;font-family:'Space Mono',monospace;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.03em;color:#111;background:var(--gold);border:1.5px solid #000;padding:3px 8px;border-radius:3px;box-shadow:2px 2px 0 #000}
    .gallery a.big{grid-column:span 2;grid-row:span 2}
    .gallery a.tall{grid-row:span 2}
    @media(max-width:820px){.gallery{grid-template-columns:repeat(2,1fr);grid-auto-rows:150px}.gallery a.big{grid-column:span 2}}

    /* TIPOS */
    .types{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .tcard{border:2px solid #000;background:var(--panel);border-radius:8px;padding:26px;transition:transform .12s,box-shadow .12s;position:relative;box-shadow:6px 6px 0 #000;display:block}
    .tcard:hover{transform:translate(-2px,-2px);box-shadow:9px 9px 0 var(--blast)}
    .tcard .ico{font-size:44px}
    .tcard .mini3d{margin:-26px -26px 14px;height:210px;border-radius:6px 6px 0 0;overflow:hidden;background:radial-gradient(circle at 50% 35%,#2a2a33,#14141a);position:relative}
    .tcard .mini3d canvas{width:100%!important;height:100%!important;display:block;cursor:grab}
    .tcard .mini3d--empty{display:grid;place-items:center;font-size:64px}
    .tcard .mini3d .m3d-spin{position:absolute;inset:0;display:grid;place-items:center;color:#9a9aa7;font-size:13px}
    .tcard .badge3d{position:absolute;top:12px;right:12px;z-index:3;background:var(--ok);color:#04240f;border:2px solid #000;border-radius:4px;padding:2px 8px;font-family:'Space Mono',monospace;font-weight:700;font-size:11px}
    .tcard h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;font-size:27px;text-transform:uppercase;margin:12px 0 6px}
    .tcard p{color:var(--muted);font-size:14px;min-height:40px}
    .tcard .price{font-family:'Space Mono',monospace;color:var(--gold);margin-top:12px;font-size:14px}
    .tcard .go{margin-top:14px;font-family:'Big Shoulders Display',sans-serif;font-weight:700;font-size:16px;letter-spacing:.03em;text-transform:uppercase;display:flex;align-items:center;gap:8px;color:var(--blast-2)}
    .tcard .soon{position:absolute;top:14px;right:14px;font-family:'Space Mono',monospace;font-size:10px;background:var(--hot);color:#fff;border:1.5px solid #000;padding:3px 8px;border-radius:3px;text-transform:uppercase}
    @media(max-width:820px){.types{grid-template-columns:1fr}}

    /* COLECCIONES */
    .cols{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    .col-card{position:relative;aspect-ratio:4/5;border:2px solid #000;border-radius:6px;overflow:hidden;display:block;box-shadow:4px 4px 0 #000;transition:transform .12s,box-shadow .12s}
    .col-card:hover{transform:translate(-2px,-2px);box-shadow:7px 7px 0 var(--gold)}
    .col-card img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:.45s}
    .col-card:hover img{transform:scale(1.06)}
    .col-card .ov{position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(8,8,10,.9))}
    .col-card h3{position:absolute;left:12px;right:12px;bottom:14px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:21px;color:#fff;margin:0}
    @media(max-width:820px){.cols{grid-template-columns:1fr 1fr}}
    @media(max-width:520px){.cols{grid-template-columns:1fr}}

    /* TESTIMONIOS (paper) */
    .quotes{background:var(--paper);color:#141418;border-top:3px solid #000;border-bottom:3px solid #000}
    .quotes .head h2{color:#141418}
    .qgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .q{position:relative;background:#fff;border:2px solid #000;border-radius:12px;padding:28px 24px 22px;box-shadow:6px 6px 0 #111;transition:transform .14s,box-shadow .14s}
    .q::before{content:'“';position:absolute;top:-12px;right:18px;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:80px;line-height:1;color:var(--gold);pointer-events:none}
    .q:hover{transform:translate(-2px,-3px);box-shadow:9px 9px 0 var(--gold)}
    .q .st{color:var(--gold);margin-bottom:12px;font-size:18px;letter-spacing:2px;-webkit-text-stroke:.6px #b58a1f}
    .q p{font-family:'Newsreader',Georgia,serif;font-style:italic;font-size:19px;font-weight:500;color:#20232c;margin-bottom:20px;line-height:1.5;position:relative;z-index:1}
    .q .who{display:flex;align-items:center;gap:12px;border-top:1px solid #e6e0d2;padding-top:16px}
    .q .who img{width:48px;height:48px;border-radius:50%;border:2px solid #000;object-fit:cover}
    .q .who b{display:block;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:16px;letter-spacing:.02em}
    .q .who span{font-family:'Space Mono',monospace;font-size:11px;color:#6a7080}
    @media(max-width:820px){.qgrid{grid-template-columns:1fr}}

    /* COLEGIOS */
    .col-grid{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
    .col-grid .ph{border:2px solid #000;border-radius:8px;overflow:hidden;aspect-ratio:5/4;box-shadow:8px 8px 0 var(--gold)}
    .col-grid .ph img{width:100%;height:100%;object-fit:cover}
    .feat{display:flex;gap:14px;margin-bottom:20px}
    .feat .fi{flex-shrink:0;font-size:24px;line-height:1}
    .feat h4{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:18px;margin-bottom:2px}
    .feat p{color:var(--muted);font-size:14px}
    @media(max-width:820px){.col-grid{grid-template-columns:1fr;gap:26px}}

    /* FAQ */
    .faq{max-width:840px}
    .faq details{border:2px solid #000;border-radius:6px;padding:0 20px;margin-bottom:12px;background:var(--panel);box-shadow:4px 4px 0 #000}
    .faq summary{cursor:pointer;list-style:none;padding:18px 0;font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:18px;display:flex;justify-content:space-between;align-items:center;gap:12px}
    .faq summary::-webkit-details-marker{display:none}
    .faq summary::after{content:'+';color:var(--gold);font-size:26px;font-family:'Space Mono',monospace;transition:.2s}
    .faq details[open] summary::after{transform:rotate(45deg)}
    .faq .fa{padding:0 0 18px;color:var(--muted);font-size:15px;line-height:1.7}

    /* SEO */
    .seo-content{background:var(--ink-2);border-top:2px solid #000}
    .seo-content .wrap{max-width:840px}
    .seo-content h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(24px,3.4vw,40px);margin:0 0 16px}
    .seo-content p{color:#c2c2cd;margin-bottom:14px;font-size:16px;line-height:1.8}
    .seo-content strong{color:var(--paper)}

    /* CTA final */
    .cta-final{background:radial-gradient(700px 340px at 50% -20%,rgba(233,184,63,.18),transparent 60%),#0d0d0f;border-top:3px solid var(--gold);border-bottom:3px solid var(--gold);text-align:center;color:var(--paper)}
    .cta-final h2{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(38px,7vw,84px);line-height:.86;margin-bottom:14px;color:var(--gold)}
    .cta-final p{color:#cfc9bd;margin-bottom:26px;font-size:17px}
    /* Entrada premium del CTA final */
    .cta-final .ey,.cta-final h2,.cta-final p{opacity:0;transform:translateY(20px)}
    .cta-final .wrap.reveal.in .ey{opacity:1;transform:none;transition:.6s ease}
    .cta-final .wrap.reveal.in h2{opacity:1;transform:none;transition:.7s cubic-bezier(.2,.8,.2,1) .1s}
    .cta-final .wrap.reveal.in p{opacity:1;transform:none;transition:.6s ease .22s}
    .cta-final h2{background:linear-gradient(90deg,var(--gold),var(--blast-2),var(--gold));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;background-size:200% auto}
    .cta-final .wrap.reveal.in h2{animation:ctashine 4s linear 1s infinite}
    @keyframes ctashine{to{background-position:200% center}}
    .cta-btns .btn{opacity:0;transform:translateY(18px) scale(.95)}
    .cta-final .wrap.reveal.in .cta-btns .btn{opacity:1;transform:none;transition:opacity .5s ease,transform .55s cubic-bezier(.2,.8,.3,1.3)}
    .cta-final .wrap.reveal.in .cta-btns .btn:nth-child(1){transition-delay:.34s}
    .cta-final .wrap.reveal.in .cta-btns .btn:nth-child(2){transition-delay:.46s}
    .cta-final .wrap.reveal.in .cta-btns .btn:nth-child(3){transition-delay:.58s}
    @media(prefers-reduced-motion:reduce){.cta-final .ey,.cta-final h2,.cta-final p,.cta-btns .btn{opacity:1;transform:none}.cta-final h2{animation:none}}

    /* FOOTER */
    /* Portales */
    .portales-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
    @media(max-width:840px){.portales-grid{grid-template-columns:1fr}}
    .portal-card{background:var(--ink-2);border:1px solid rgba(244,239,227,.1);border-radius:18px;padding:22px;display:flex;flex-direction:column;gap:18px}
    .portal-info h3{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:22px;margin-bottom:10px}
    .portal-info ul{list-style:none;padding:0;margin:0 0 16px;display:flex;flex-direction:column;gap:8px}
    .portal-info li{color:#cfc9bd;font-size:14.5px;padding-left:22px;position:relative}
    .portal-info li::before{content:"✓";position:absolute;left:0;color:var(--gold);font-weight:800}
    /* Mockup ventana (colegio) */
    .pc-window{background:#0f0f13;border:1px solid rgba(244,239,227,.12);border-radius:12px;overflow:hidden;box-shadow:0 14px 40px rgba(0,0,0,.45)}
    .pc-bar{display:flex;align-items:center;gap:6px;padding:10px 14px;background:#17171d;border-bottom:1px solid rgba(244,239,227,.08);font-size:12px;color:var(--muted)}
    .pc-bar span{width:9px;height:9px;border-radius:50%;background:#3a3a44}.pc-bar b{margin-left:8px;color:#e7e3d8;font-weight:600}
    .pc-body{padding:18px;display:flex;gap:16px;align-items:center}
    .pc-stat{flex:none;text-align:center;background:rgba(233,184,63,.1);border:1px solid rgba(233,184,63,.35);border-radius:12px;padding:14px 16px}
    .pc-stat .pcn{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:30px;color:var(--gold);line-height:1}
    .pc-stat .pcl{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .pc-rows{flex:1;display:flex;flex-direction:column;gap:7px}
    .pc-row{display:flex;justify-content:space-between;align-items:center;background:#16161c;border:1px solid rgba(244,239,227,.07);border-radius:8px;padding:9px 12px;font-size:13px;color:#e7e3d8}
    .pc-row i{font-style:normal;font-size:11px;color:var(--gold);background:rgba(233,184,63,.12);padding:2px 8px;border-radius:999px}
    /* Mockup teléfono (estudiante) */
    .pc-phone{width:220px;margin:0 auto;background:#0f0f13;border:6px solid #17171d;border-radius:26px;padding:14px 14px 18px;box-shadow:0 16px 44px rgba(0,0,0,.5)}
    .pcp-top{text-align:center;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-weight:800;color:var(--gold);font-size:15px;margin-bottom:12px}
    .pcp-profile{display:flex;align-items:center;gap:10px;margin-bottom:12px}
    .pcp-av{width:40px;height:40px;border-radius:50%;background:rgba(233,184,63,.16);border:1px solid var(--gold);display:grid;place-items:center;font-size:20px}
    .pcp-profile b{display:block;color:#f4efe3;font-size:14px}.pcp-profile span{color:var(--muted);font-size:12px}
    .pcp-prog{height:7px;background:#26262e;border-radius:999px;overflow:hidden;margin-bottom:6px}
    .pcp-prog i{display:block;height:100%;width:80%;background:var(--gold)}
    .pcp-tag{font-size:12px;color:#e7e3d8;margin-bottom:12px}
    .pcp-feed{display:flex;flex-direction:column;gap:7px}
    .pcp-feed .fp{display:flex;align-items:center;gap:8px;background:#16161c;border:1px solid rgba(244,239,227,.07);border-radius:8px;padding:8px 10px;font-size:12px;color:#cfc9bd}
    .pcp-feed .fa{font-size:14px}
    footer{background:var(--ink-2);border-top:3px solid var(--gold);color:var(--muted);font-size:14px}
    .foot-cta{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:30px 0;margin-bottom:10px;border-bottom:1px solid rgba(244,239,227,.1)}
    .foot-cta .fc-eyebrow{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--gold);margin-bottom:6px}
    .foot-cta .fc-title{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(22px,3.4vw,34px);color:var(--paper);line-height:.95}
    .foot-cta .fc-btns{display:flex;gap:12px;flex-wrap:wrap}
    .foot-cta a{display:inline-flex;align-items:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:16px;padding:13px 22px;border:2px solid #000;border-radius:9px;text-decoration:none;box-shadow:4px 4px 0 #000;transition:transform .1s,box-shadow .1s}
    .foot-cta a:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .foot-cta .fc-gold{background:var(--gold);color:#1c1503} .foot-cta .fc-wa{background:#25d366;color:#04240f}
    .foot-cta .fc-wa svg{width:18px;height:18px}
    .foot-top{display:grid;grid-template-columns:1.4fr 1fr 1fr 1.2fr;gap:36px;padding:56px 0 40px}
    .foot-brand .fb-logo{display:flex;align-items:center;gap:12px;margin-bottom:14px}
    .foot-brand .mk{width:44px;height:44px;border-radius:10px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:24px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .foot-brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:26px;letter-spacing:.04em;color:var(--paper);line-height:.85}
    .foot-brand p{max-width:300px;line-height:1.6;margin-bottom:16px}
    .foot-soc{display:flex;gap:10px}
    .foot-soc a{width:38px;height:38px;border:2px solid var(--line);border-radius:9px;display:grid;place-items:center;color:var(--paper);transition:.15s}
    .foot-soc a:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-2px)}
    .foot-col h4{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:15px;letter-spacing:.06em;color:var(--gold);margin-bottom:14px}
    .foot-col a,.foot-col div{display:block;color:var(--muted);margin-bottom:10px;line-height:1.4}
    .foot-col a:hover{color:var(--paper)}
    .foot-col .lbl{font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6a6a72;margin-bottom:2px}
    .foot-cities{border-top:1px solid var(--line);padding:22px 0}
    .foot-cities .lbl{font-family:'Space Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--gold);margin-bottom:10px}
    .foot-cities .links{display:flex;flex-wrap:wrap;gap:6px 20px}
    .foot-cities a{color:var(--muted)} .foot-cities a:hover{color:var(--gold)}
    .foot-bottom{border-top:1px solid var(--line);padding:20px 0;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;font-family:'Space Mono',monospace;font-size:12px}
    .foot-bottom a{color:var(--muted)} .foot-bottom a:hover{color:var(--gold)}
    .foot-legal{display:flex;gap:16px;flex-wrap:wrap}
    .foot-credit{text-align:center;padding:14px 0 4px;border-top:1px solid var(--line);margin-top:6px;font-family:'Space Mono',monospace;font-size:12px;color:var(--muted)}
    .foot-credit a{color:var(--gold);text-decoration:none} .foot-credit a:hover{text-decoration:underline}
    @media(max-width:820px){.foot-top{grid-template-columns:1fr 1fr;gap:28px}}
    @media(max-width:520px){.foot-top{grid-template-columns:1fr}}

    .fab{position:fixed;right:20px;bottom:20px;z-index:80;display:inline-flex;align-items:center;gap:11px;background:#25d366;color:#04240f;height:60px;padding:0 22px 0 12px;border-radius:32px;border:2px solid #000;box-shadow:4px 4px 0 #000;text-decoration:none;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:16px;transition:transform .12s,box-shadow .12s}
    .fab .ficon{width:38px;height:38px;flex-shrink:0;display:grid;place-items:center;background:#04240f;color:#25d366;border-radius:50%}
    .fab:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .fab::after{content:'';position:absolute;left:8px;bottom:9px;width:42px;height:42px;border-radius:50%;border:2px solid #25d366;opacity:.7;animation:fabpulse 1.9s ease-out infinite;pointer-events:none}
    @keyframes fabpulse{0%{transform:scale(.85);opacity:.7}100%{transform:scale(1.8);opacity:0}}
    @media(max-width:560px){.fab{padding:0;width:60px;justify-content:center;border-radius:50%}.fab .ftxt{display:none}}
    @media(prefers-reduced-motion:reduce){.fab::after{animation:none;display:none}}

    /* Popup chaqueta gratis */
    .pop-ov{position:fixed;inset:0;z-index:200;background:rgba(6,7,10,.82);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;padding:22px}
    .pop-ov.on{display:flex;animation:popfade .25s ease}
    @keyframes popfade{from{opacity:0}to{opacity:1}}
    .pop{position:relative;max-width:430px;width:100%;background:var(--panel);border:3px solid #000;border-radius:16px;padding:34px 28px 26px;box-shadow:12px 12px 0 var(--gold);text-align:center;animation:poppop .3s cubic-bezier(.2,.7,.3,1.4)}
    @keyframes poppop{from{transform:translateY(20px) scale(.95);opacity:0}to{transform:none;opacity:1}}
    .pop .x{position:absolute;top:10px;right:14px;background:none;border:none;color:var(--muted);font-size:28px;cursor:pointer;line-height:1}
    .pop .x:hover{color:var(--gold)}
    .pop .gift{font-size:46px;line-height:1}
    .pop h3{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:32px;line-height:.9;margin:10px 0 8px}
    .pop h3 .g{color:var(--gold)}
    .pop p{color:var(--muted);font-size:14px;margin-bottom:16px}
    .pop input{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);border-radius:8px;padding:12px;margin-bottom:10px;font-size:15px}
    .pop input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.2)}
    .pop .go{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:9px;background:var(--ok);color:#04240f;border:2px solid #000;border-radius:9px;padding:14px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:18px;cursor:pointer;box-shadow:4px 4px 0 #000;transition:transform .1s,box-shadow .1s}
    .pop .go:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .pop .mini{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);margin-top:10px}

    .reveal{opacity:0;transform:translateY(22px);transition:opacity .6s ease,transform .6s ease}
    .reveal.in{opacity:1;transform:none}
    :focus-visible{outline:3px solid var(--gold);outline-offset:2px}

    /* Entrada escalonada de tarjetas (movimiento juvenil) */
    .steps.reveal > *,.gar-grid.reveal > *,.cols.reveal > *,.qgrid.reveal > *,.gallery.reveal > *{opacity:0;transform:translateY(20px) scale(.97);transition:opacity .55s ease,transform .55s cubic-bezier(.2,.7,.3,1.3)}
    .steps.reveal.in > *,.gar-grid.reveal.in > *,.cols.reveal.in > *,.qgrid.reveal.in > *,.gallery.reveal.in > *{opacity:1;transform:none}
    .reveal.in > *:nth-child(2){transition-delay:.07s}
    .reveal.in > *:nth-child(3){transition-delay:.14s}
    .reveal.in > *:nth-child(4){transition-delay:.21s}
    .reveal.in > *:nth-child(5){transition-delay:.28s}
    .reveal.in > *:nth-child(6){transition-delay:.35s}
    .reveal.in > *:nth-child(7){transition-delay:.42s}
    .reveal.in > *:nth-child(8){transition-delay:.49s}

    /* Stickers del hero flotando */
    .hero-stickers .sticker:nth-child(1){animation:flA 3.4s ease-in-out infinite}
    .hero-stickers .sticker:nth-child(2){animation:flB 3.8s ease-in-out infinite .3s}
    .hero-stickers .sticker:nth-child(3){animation:flC 3.2s ease-in-out infinite .6s}
    @keyframes flA{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-7px) rotate(-3deg)}}
    @keyframes flB{0%,100%{transform:translateY(0) rotate(2deg)}50%{transform:translateY(-6px) rotate(2deg)}}
    @keyframes flC{0%,100%{transform:translateY(0) rotate(-1.5deg)}50%{transform:translateY(-8px) rotate(-1.5deg)}}

    /* Micro-interacción en botones sólidos */
    .btn-gold:hover,.nav-cta:hover{filter:brightness(1.04)}

    @media(prefers-reduced-motion:reduce){
        .reveal{opacity:1;transform:none;transition:none}
        .steps.reveal > *,.gar-grid.reveal > *,.cols.reveal > *,.qgrid.reveal > *,.gallery.reveal > *{opacity:1;transform:none;transition:none}
        .ticker .run,.hero-stickers .sticker{animation:none}
        .btn:hover,.tcard:hover,.col-card:hover,.gallery a:hover img,.fab:hover,.nav-cta:hover{transform:none}
    }
</style>
</head>
<body>

<header id="siteHead">
    <div class="util">
        <div class="util-inner">
            <div class="u-left mono">★ Sé el primero de tu colegio y llévate tu chaqueta <b style="color:var(--gold)">GRATIS</b></div>
            <div class="u-right">
                <a class="u-hide" href="tel:{{ $phone }}">{!! $svgPhone !!} {{ $phone }}</a>
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola Glauren! 👋 Quiero información sobre las chaquetas de mi promoción.') }}" target="_blank" rel="noopener">{!! $svgWa !!} WhatsApp</a>
                <span class="u-soc u-hide">
                    <a href="{{ Setting::get('social_instagram', 'https://www.instagram.com/chaquetaprom_/') }}" target="_blank" rel="noopener" aria-label="Instagram">{!! $svgIg !!}</a>
                    <a href="{{ Setting::get('social_tiktok', 'https://www.tiktok.com/@chaquetaprom') }}" target="_blank" rel="noopener" aria-label="TikTok">{!! $svgTk !!}</a>
                    <a href="{{ Setting::get('social_facebook', 'https://www.facebook.com/glaurenztienda') }}" target="_blank" rel="noopener" aria-label="Facebook">{!! $svgFb !!}</a>
                </span>
            </div>
        </div>
    </div>
    <nav class="mainnav">
        <a href="{{ route('home') }}" class="brand" aria-label="Glauren inicio">
            @php $brandLogo = Setting::get('brand_logo'); @endphp
            @if($brandLogo)
                <img src="{{ $brandLogo }}" alt="{{ Setting::get('brand_name', 'Glauren') }}" style="height:46px;width:auto">
            @else
                <span class="mk">G</span>
                <span><span class="wm">{{ Setting::get('brand_name', 'Glauren') }}</span><span class="tg">Chaquetas de prom</span></span>
            @endif
        </a>
        <div class="navlinks" id="navLinks">
            @foreach($nav as $it)
                @if(trim((string) $it->url, '/') === 'contacto') @continue @endif
                @if($it->children->count())
                    <div class="navdd">
                        <span class="ddlabel">{{ $it->label }} <span style="font-size:.7em">▾</span></span>
                        <div class="ddmenu">
                            @foreach($it->children as $ch)<a href="{{ $ch->url }}">{{ $ch->label }}</a>@endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $it->url }}" class="{{ trim($it->url, '/') === trim(request()->path(), '/') ? 'active' : '' }}">{{ $it->label }}</a>
                @endif
            @endforeach
            <span class="nav-sep"></span>
            <a href="{{ route('contacto') }}" class="nav-contact {{ request()->routeIs('contacto') ? 'active' : '' }}">✉️ Contáctenos</a>
            <a href="{{ route('personalizar') }}" class="nav-cta">Diseña la tuya →</a>
        </div>
        <button class="navtoggle" id="navToggle" aria-label="Abrir menú" onclick="document.body.classList.toggle('nav-open')">☰</button>
    </nav>
</header>

@php
    // Año de la promoción: se adelanta al siguiente año desde AGOSTO (4 meses antes de diciembre)
    $promoYear = (int) date('n') >= 8 ? (int) date('Y') + 1 : (int) date('Y');
    $heroAccent = str_replace(['{año}', '{ano}', '{year}'], $promoYear, Setting::get('home_hero_accent', 'Promoción exclusiva {año}'));
@endphp
<header class="hero-bg" style="background-image:url('{{ $heroImg }}')">
    <div class="hero-grad"></div>
    <div class="hero-inner">
        <div class="hero-stickers">
            <span class="sticker st-gold">★ Class of '{{ substr($promoYear, -2) }}</span>
            <span class="sticker st-blast">Prom {{ $promoYear }}</span>
            <span class="sticker st-hot">🎁 Gratis al 1er colegio</span>
        </div>
        <h1 class="hero-h1">{{ Setting::get('home_hero_title', 'Chaquetas que marcan') }}<span class="fill">{{ $heroAccent }}</span></h1>
        <p class="hero-sub">{{ Setting::get('home_hero_subtitle', 'Diséñala en 3D, síguela mientras la fabricamos —con fotos reales— y págala en línea. Sin misterios.') }}</p>
        <div class="cta-actions">
            <a href="{{ route('personalizar') }}" class="btn btn-gold">Diseña tu chaqueta en 3D</a>
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola Glauren! 👋 Quiero información sobre las chaquetas de mi promoción.') }}" target="_blank" rel="noopener" class="btn btn-wa">{!! $svgWa !!} WhatsApp</a>
            <a href="{{ route('seguimiento') }}" class="btn btn-line">Busca tu chaqueta</a>
        </div>
    </div>
</header>

@include('chaquetas.partials.home-blocks', ['position' => 'after_hero'])

<div class="ticker ticker--blast" aria-hidden="true">
    <div class="run">
        <span>Diseña en 3D</span><span class="dot">✦</span><span>Grado once</span><span class="dot">✦</span><span>Prom 2026</span><span class="dot">✦</span><span>Bordados premium</span><span class="dot">✦</span><span>Entrega récord</span><span class="dot">✦</span><span>Toda Colombia</span><span class="dot">✦</span>
        <span>Diseña en 3D</span><span class="dot">✦</span><span>Grado once</span><span class="dot">✦</span><span>Prom 2026</span><span class="dot">✦</span><span>Bordados premium</span><span class="dot">✦</span><span>Entrega récord</span><span class="dot">✦</span><span>Toda Colombia</span><span class="dot">✦</span>
    </div>
</div>

@php $SEC = []; @endphp
@php ob_start(); @endphp
<section>
    <div class="wrap">
        <div class="gar-grid reveal">
            @foreach($garRows as $g)
            <div class="gar"><div class="em">{{ $g[0] ?? '' }}</div><div><h4>{{ $g[1] ?? '' }}</h4><p>{{ $g[2] ?? '' }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>
@php $SEC['garantias'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section style="padding-top:20px">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Cómo funciona</span>
            <h2>De la idea<br>a tus hombros</h2>
            <p>Un proceso claro, en orden, con visibilidad real en cada etapa.</p>
        </div>
        <div class="steps reveal">
            @foreach($stepRows as $i => $s)
            <div class="step"><div class="s-top"><span class="no">{{ $i+1 }}</span><span class="ic">{{ $s[0] ?? '' }}</span></div><h3>{{ $s[1] ?? '' }}</h3><p>{{ $s[2] ?? '' }}</p></div>
            @endforeach
        </div>

        <div class="helpband reveal">
            <div class="hb-txt">
                <span class="sticker st-blast" style="transform:rotate(-2deg)">Sin compromiso</span>
                <h3>¿No sabes diseñar? Lo hacemos por ti</h3>
                <p>Cuéntanos tu idea y la diseñamos desde cero. Y si <b>ya tienes tu diseño</b>, envíanoslo por WhatsApp y te cotizamos al instante.</p>
            </div>
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, quiero ayuda con el diseño / cotizar mi chaqueta de prom.') }}" target="_blank" rel="noopener" class="btn btn-wa">{!! $svgWa !!} Escríbenos por WhatsApp</a>
        </div>
    </div>
</section>
@php $SEC['proceso'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section class="numbers">
    <div class="wrap">
        <div class="num-grid reveal">
            @foreach($numRows as $nr)
                @php $to = (int) preg_replace('/\D/', '', $nr[0] ?? '0'); @endphp
                <div class="num"><div class="nb"><span class="n" data-to="{{ $to }}">0</span><span class="plus">+</span></div><div class="l">{{ $nr[1] ?? '' }}</div></div>
            @endforeach
        </div>
        <div class="trusted reveal">
            <div class="lbl">{{ $trustedLabel }}</div>
            <div class="logos collapsed" id="trustedLogos">
                @foreach($trustedList as $t)<span>{{ $t }}</span>@endforeach
            </div>
            <button type="button" class="logos-toggle" id="logosToggle">Ver todos ({{ count($trustedList) }}) ↓</button>
        </div>
    </div>
</section>
@php $SEC['numbers'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
@php
    // Sección de video del home (administrable desde Admin → Textos/Home)
    $videoUrl = trim((string) Setting::get('home_video_url', ''));
    $tiktokProfile = Setting::get('social_tiktok', 'https://www.tiktok.com/@chaquetaprom');
    $embed = null; $embedType = null;
    if ($videoUrl) {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|shorts/|embed/))([A-Za-z0-9_-]{6,})~', $videoUrl, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1]; $embedType = 'yt';
        } elseif (preg_match('~tiktok\.com/.*?/video/(\d+)~', $videoUrl, $m) || preg_match('~tiktok\.com/embed/v2/(\d+)~', $videoUrl, $m)) {
            $embed = 'https://www.tiktok.com/embed/v2/'.$m[1]; $embedType = 'tk';
        }
    }
@endphp
@if($embed || $tiktokProfile)
<section class="video-sec">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// En movimiento</span>
            <h2>Míranos en acción</h2>
            <p>Diseños reales, entregas reales, promociones felices. Síguenos y no te pierdas nada.</p>
        </div>
        @if($embed)
            <div class="video-frame reveal {{ $embedType === 'tk' ? 'is-tk' : '' }}">
                <iframe src="{{ $embed }}" title="Video Glauren" loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
            </div>
        @else
            <a href="{{ $tiktokProfile }}" target="_blank" rel="noopener" class="video-cta reveal">
                <span class="vc-ic">{!! $svgTk !!}</span>
                <span class="vc-txt"><b>Míranos en TikTok</b><small>@chaquetaprom · videos, ideas y promos</small></span>
                <span class="vc-go">Ver →</span>
            </a>
        @endif
        @if($embed)
            <div class="reveal" style="text-align:center;margin-top:18px">
                <a href="{{ $tiktokProfile }}" target="_blank" rel="noopener" class="video-follow">{!! $svgTk !!} Síguenos en TikTok</a>
            </div>
        @endif
    </div>
</section>
@endif
@php $SEC['video'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section class="gal-sec" id="gal">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Promociones reales</span>
            <h2>Ya visten Glauren</h2>
            <p>Colegios de Bogotá, Medellín, Cali y toda Colombia. Estas son de verdad.</p>
        </div>
        @php $showcaseBanner = Setting::get('showcase_banner'); @endphp
        @if($showcaseBanner)
            <a href="{{ route('personalizar') }}" class="promo-banner reveal">
                <img src="{{ $showcaseBanner }}" alt="Promoción {{ date('Y') }} · Glauren" loading="lazy" decoding="async">
            </a>
        @endif
        <div class="gallery reveal">
            @foreach($gallery as $i => $g)
                <a href="{{ route('personalizar') }}" class="{{ $i===0 ? 'big' : ($i===5 ? 'tall' : '') }}">
                    <img src="{{ $g[0] }}" alt="Chaquetas de prom {{ $g[1] }} · Glauren Colombia" loading="lazy" decoding="async">
                    <span>{{ $g[1] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@php $SEC['gallery'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section>
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Modelos</span>
            <h2>Elige tu estilo</h2>
            <p>Cada modelo se personaliza por completo en el diseñador 3D.</p>
        </div>
        <div class="types">
            @foreach($types as $t)
                <a href="{{ route('design', $t->slug) }}" class="tcard reveal">
                    @if($t->hasModel())
                        <span class="badge3d">✓ 3D</span>
                        <div class="mini3d" data-model="/storage/{{ $t->model_path }}" data-icon="{{ $t->icon }}"></div>
                    @else
                        <span class="soon">Pronto</span>
                        <div class="mini3d mini3d--empty">{{ $t->icon }}</div>
                    @endif
                    <h3>{{ $t->name }}</h3>
                    @if($t->description)<p>{{ $t->description }}</p>@endif
                    <div class="go">Personalizar →</div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@php $SEC['tipos'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
@if($collections->count())
<section style="padding-top:20px">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Colecciones</span>
            <h2>Nuestros estilos</h2>
            <p>Beisboleras, estructuradas, hoodies, camisas y más — todo personalizable.</p>
        </div>
        <div class="cols reveal">
            @foreach($collections as $c)
                <a href="{{ $c->targetUrl() }}" class="col-card">
                    <img src="{{ $c->imageUrl() }}" alt="Chaquetas de prom {{ $c->title }} · Glauren" loading="lazy" decoding="async">
                    <div class="ov"></div>
                    <h3>{{ $c->title }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@php $SEC['collections'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section class="quotes paper">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Testimonios</span>
            <h2>Lo que dicen<br>las promociones</h2>
        </div>
        <div class="qgrid reveal">
            @foreach($testimonios as $q)
                <div class="q">
                    <div class="st">{{ str_repeat('★', $q[4] ?? 5) }}</div>
                    <p>“{{ $q[0] }}”</p>
                    <div class="who">
                        @if(!empty($q[3]))
                            <img src="{{ $q[3] }}" alt="{{ $q[1] }}" loading="lazy" decoding="async">
                        @else
                            <span style="width:44px;height:44px;border-radius:50%;background:#e7dcc4;color:#8a6d1f;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:800;flex-shrink:0">{{ mb_substr($q[1],0,1) }}</span>
                        @endif
                        <div><b>{{ $q[1] }}</b><span>{{ $q[2] }}</span></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@php $SEC['testimonios'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section>
    <div class="wrap">
        <div class="col-grid">
            <div class="reveal">
                <span class="ey">// Para colegios</span>
                <h2 class="display" style="font-size:clamp(32px,4.4vw,56px);margin:12px 0 26px">La cuenta de<br>tu colegio</h2>
                <div class="feat"><div class="fi">✍️</div><div><h4>Contrato y firma en línea</h4><p>Genera y firma el contrato de la promoción desde la plataforma.</p></div></div>
                <div class="feat"><div class="fi">📸</div><div><h4>Proceso con fotos</h4><p>Avance de fabricación y fotos finales, siempre a la vista.</p></div></div>
                <div class="feat"><div class="fi">🪪</div><div><h4>Alumnos por documento</h4><p>Cada estudiante verifica con su tarjeta de identidad y ve lo suyo.</p></div></div>
                <a href="{{ route('colegios') }}" class="btn btn-blast" style="margin-top:8px">Conocer el portal</a>
            </div>
            <div class="ph reveal"><img src="https://sp-ao.shortpixel.ai/client/to_auto,q_glossy,ret_img,w_1024,h_976/https://chaquetasdeprom.com/wp-content/uploads/2025/03/chaqueta-prom-madrid-cundinamarca-1024x976.jpg" alt="Promoción con chaquetas Glauren" loading="lazy" decoding="async"></div>
        </div>
    </div>
</section>
@php $SEC['colegios'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
@if($faqs->count())
<section style="padding-top:20px">
    <div class="wrap">
        <div class="head reveal">
            <span class="ey">// Dudas</span>
            <h2>Preguntas frecuentes</h2>
        </div>
        <div class="faq reveal">
            @foreach($faqs as $f)
                <details>
                    <summary>{{ $f->question }}</summary>
                    <div class="fa">{!! nl2br(e($f->answer)) !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
@php $SEC['faq'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
@if(!empty($seoContent))
<section class="seo-content">
    <div class="wrap reveal">{!! $seoContent !!}</div>
</section>
@endif
@php $SEC['seo'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section class="portales-sec" style="padding-top:20px">
    <div class="wrap reveal">
        <div style="text-align:center;margin-bottom:34px">
            <span class="ey" style="color:var(--gold)">// No vendemos una prenda, vendemos una experiencia</span>
            <h2 class="sec-title">Dos portales, toda una experiencia</h2>
            <p style="color:var(--muted);max-width:580px;margin:10px auto 0">Con Glauren tu promoción vive todo el proceso: el colegio administra su pedido y cada estudiante sigue su chaqueta, arma su perfil, sube sus fotos y comparte con su curso. Más que una chaqueta — un recuerdo.</p>
        </div>
        <div class="portales-grid">
            {{-- Portal del colegio --}}
            <div class="portal-card">
                <div class="pc-window">
                    <div class="pc-bar"><span></span><span></span><span></span><b>Portal del colegio</b></div>
                    <div class="pc-body">
                        <div class="pc-stat"><div class="pcn">80%</div><div class="pcl">Producción</div></div>
                        <div class="pc-rows">
                            <div class="pc-row"><span>✅ Contrato firmado</span></div>
                            <div class="pc-row"><span>🧵 En producción</span><i>fotos</i></div>
                            <div class="pc-row"><span>💰 Pagos 32/40</span></div>
                            <div class="pc-row"><span>👥 40 estudiantes</span></div>
                        </div>
                    </div>
                </div>
                <div class="portal-info">
                    <h3>🏫 Para el colegio</h3>
                    <ul>
                        <li>Contrato y firma en línea</li>
                        <li>Avance de fabricación con fotos reales</li>
                        <li>Control de pagos por estudiante</li>
                        <li>Lista del curso y medidas</li>
                    </ul>
                    <a href="{{ route('portal.login') }}" class="btn btn-gold" style="margin-top:6px">Entrar al portal →</a>
                </div>
            </div>

            {{-- Portal del estudiante --}}
            <div class="portal-card">
                <div class="pc-phone">
                    <div class="pcp-top">Mi chaqueta</div>
                    <div class="pcp-profile">
                        <div class="pcp-av">🙂</div>
                        <div><b>Valentina</b><span>11B · San José</span></div>
                    </div>
                    <div class="pcp-prog"><i></i></div>
                    <div class="pcp-tag">🧵 En producción · 80%</div>
                    <div class="pcp-feed">
                        <div class="fp"><span class="fa">📸</span> Vale subió una foto</div>
                        <div class="fp"><span class="fa">💬</span> “¡Ya casi chicos! 🔥”</div>
                        <div class="fp"><span class="fa">🎓</span> Curso 11B · 38 miembros</div>
                    </div>
                </div>
                <div class="portal-info">
                    <h3>🎒 Para el estudiante</h3>
                    <ul>
                        <li>Seguimiento de su pedido con fotos</li>
                        <li>Su <b>perfil</b> con foto y datos</li>
                        <li>Subir sus propias fotos</li>
                        <li>Muro del curso: escribirse entre compañeros</li>
                    </ul>
                    <a href="{{ route('student.login') }}" class="btn btn-line" style="margin-top:6px">Entrar a mi portal →</a>
                </div>
            </div>
        </div>
    </div>
</section>
@php $SEC['portales'] = ob_get_clean(); @endphp

@php ob_start(); @endphp
<section class="cta-final">
    <div class="wrap reveal">
        <span class="ey" style="color:var(--gold)">// Empieza hoy</span>
        <h2>Tu promoción,<br>a un clic</h2>
        <p>Diseña en 3D, o escríbenos y te asesoramos para tu curso. También cotizamos tu propio diseño.</p>
        <div class="cta-btns" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('personalizar') }}" class="btn btn-gold">Diseña tu chaqueta</a>
            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, quiero cotizar chaquetas de prom para mi curso.') }}" target="_blank" rel="noopener" class="btn btn-wa">{!! $svgWa !!} Cotiza por WhatsApp</a>
            <a href="{{ route('seguimiento') }}" class="btn btn-line">Busca tu chaqueta</a>
        </div>
    </div>
</section>
@php $SEC['cta_final'] = ob_get_clean(); @endphp

{{-- Orden y visibilidad de las secciones (Admin → Diseño del Home) --}}
@php
    $sectionMeta = [
        'garantias'=>'Garantías', 'proceso'=>'Proceso', 'numbers'=>'Números + colegios',
        'video'=>'Video', 'gallery'=>'Galería', 'tipos'=>'Modelos', 'collections'=>'Colecciones',
        'testimonios'=>'Testimonios', 'colegios'=>'Para colegios', 'portales'=>'Portales (colegio/estudiante)', 'faq'=>'Preguntas frecuentes',
        'seo'=>'Texto SEO', 'cta_final'=>'CTA final',
    ];
    $defaultOrder = array_keys($sectionMeta);
    $saved = json_decode((string) Setting::get('home_layout', ''), true);
    $layout = (is_array($saved) && $saved) ? $saved : array_map(fn ($k) => ['key' => $k, 'on' => true], $defaultOrder);
    $seenKeys = array_column($layout, 'key');
    foreach ($defaultOrder as $k) { if (! in_array($k, $seenKeys, true)) { $layout[] = ['key' => $k, 'on' => true]; } }
@endphp

@include('chaquetas.partials.home-blocks', ['position' => 'before_gallery'])

@foreach($layout as $ln)
    @if(($ln['on'] ?? true) && ! empty($SEC[$ln['key']]))
        {!! $SEC[$ln['key']] !!}
    @endif
@endforeach

@include('chaquetas.partials.home-blocks', ['position' => 'before_footer'])

<footer>
    <div class="wrap">
        <div class="foot-cta">
            <div>
                <div class="fc-eyebrow">// Tu promoción, tu estilo</div>
                <div class="fc-title">¿List@ para diseñar la chaqueta de tu curso?</div>
            </div>
            <div class="fc-btns">
                <a href="{{ route('personalizar') }}" class="fc-gold">Diseñar en 3D →</a>
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, quiero cotizar las chaquetas de mi promoción.') }}" target="_blank" rel="noopener" class="fc-wa">{!! $svgWa !!} Cotizar</a>
            </div>
        </div>
        <div class="foot-top">
            {{-- Marca --}}
            <div class="foot-brand">
                <div class="fb-logo">
                    @if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ Setting::get('brand_name','Glauren') }}" style="height:44px">
                    @else<span class="mk">G</span><span class="wm">{{ Setting::get('brand_name', 'Glauren') }}</span>@endif
                </div>
                <p>{{ Setting::get('footer_note', 'Chaquetas de promoción de alta calidad para grado once en toda Colombia. Diseña la tuya en 3D y síguela hasta tus manos.') }}</p>
                <div class="foot-soc">
                    <a href="{{ Setting::get('social_instagram', 'https://www.instagram.com/chaquetaprom_/') }}" target="_blank" rel="noopener" aria-label="Instagram">{!! $svgIg !!}</a>
                    <a href="{{ Setting::get('social_tiktok', 'https://www.tiktok.com/@chaquetaprom') }}" target="_blank" rel="noopener" aria-label="TikTok">{!! $svgTk !!}</a>
                    <a href="{{ Setting::get('social_facebook', 'https://www.facebook.com/glaurenztienda') }}" target="_blank" rel="noopener" aria-label="Facebook">{!! $svgFb !!}</a>
                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" aria-label="WhatsApp">{!! $svgWa !!}</a>
                </div>
            </div>

            {{-- Explora --}}
            <div class="foot-col">
                <h4>Explora</h4>
                <a href="{{ route('personalizar') }}">Diseña tu chaqueta 3D</a>
                <a href="{{ route('home') }}#gal">Promociones</a>
                @foreach($nav as $it)<a href="{{ $it->url }}">{{ $it->label }}</a>@endforeach
                <a href="{{ route('seguimiento') }}">Busca tu chaqueta</a>
            </div>

            {{-- Cuentas --}}
            <div class="foot-col">
                <h4>Cuentas</h4>
                <a href="{{ route('portal.login') }}">Portal colegios</a>
                <a href="{{ route('seguimiento') }}">Seguimiento alumno</a>
                <a href="{{ route('news.index') }}">Noticias</a>
                <a href="{{ route('contacto') }}">Contáctanos</a>
            </div>

            {{-- Contacto --}}
            <div class="foot-col">
                <h4>Contacto</h4>
                <div><span class="lbl">WhatsApp</span><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">+{{ $wa }}</a></div>
                <div><span class="lbl">Teléfono</span><a href="tel:{{ $phone }}">{{ $phone }}</a></div>
                <div><span class="lbl">Correo</span><a href="mailto:{{ Setting::get('contact_email','glauren@chaquetasdeprom.com') }}">{{ Setting::get('contact_email','glauren@chaquetasdeprom.com') }}</a></div>
                <div><span class="lbl">Horario</span>{{ Setting::get('contact_hours', 'Lun–Sáb · 8am – 6pm') }}</div>
                <div><span class="lbl">Dirección</span>{{ Setting::get('contact_address', 'Bogotá, Colombia') }}</div>
            </div>
        </div>


        <div class="foot-bottom">
            <div>© {{ date('Y') }} {{ Setting::get('brand_name', 'Glauren') }} · Hecho en Colombia 🇨🇴 · Todos los derechos reservados.</div>
            <div class="foot-legal">
                <a href="{{ url('/p/terminos-y-servicios') }}">Términos y servicios</a>
                <a href="{{ url('/p/politica-de-privacidad') }}">Privacidad</a>
                <a href="{{ url('/p/garantia') }}">Garantía</a>
            </div>
        </div>
        <div class="foot-credit">
            Diseño y desarrollo web por <a href="https://wa.me/573183504386" target="_blank" rel="noopener"><b>BandoSEO</b></a> · <a href="https://wa.me/573183504386" target="_blank" rel="noopener">318 350 4386</a>
        </div>
    </div>
</footer>

<a href="https://wa.me/{{ $wa }}?text={{ urlencode('¡Hola Glauren! 👋 Quiero información sobre las chaquetas de mi promoción.') }}" target="_blank" rel="noopener" class="fab" aria-label="Escríbenos por WhatsApp"><span class="ficon">{!! $svgWa !!}</span><span class="ftxt">Escríbenos</span></a>

{{-- Popup: chaqueta o buso GRATIS al contratar --}}
<div class="pop-ov" id="promoPop">
    <div class="pop">
        <button class="x" id="popX" aria-label="Cerrar">&times;</button>
        <div class="gift">🎁</div>
        <span class="sticker st-hot" style="margin-top:6px">Sé el 1ro de tu salón</span>
        <h3>Tu chaqueta o buso<br><span class="g">GRATIS</span></h3>
        <p>Sé el <b>primero de tu salón</b> en llamarnos: si contratamos con tu colegio, <b>tu prenda es gratis para ti</b>. Déjanos tus datos y te contamos cómo.</p>
        <form id="popForm">
            <input type="text" id="popName" placeholder="Tu nombre" required>
            <input type="tel" id="popPhone" placeholder="Tu WhatsApp" required>
            <button class="go" type="submit">{!! $svgWa !!} ¡La quiero gratis!</button>
        </form>
        <div class="mini">Aplica al contratar la promoción de tu colegio · sin compromiso</div>
    </div>
</div>

<script>
    const io=new IntersectionObserver((es)=>es.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } }),{threshold:.12});
    document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
    // Contador animado
    const cObs=new IntersectionObserver((es)=>es.forEach(e=>{ if(e.isIntersecting){ e.target.querySelectorAll('.n').forEach(el=>{ const to=+el.dataset.to||0, dur=1200, t0=performance.now(); (function tick(t){ const p=Math.min(1,(t-t0)/dur); el.textContent=Math.round(to*(1-Math.pow(1-p,3))).toLocaleString('es-CO'); if(p<1)requestAnimationFrame(tick); })(t0); }); cObs.unobserve(e.target); } }),{threshold:.4});
    const ng=document.querySelector('.num-grid'); if(ng) cObs.observe(ng);

    // Popup "chaqueta o buso gratis"
    (function(){
        const ov=document.getElementById('promoPop'); if(!ov) return;
        const KEY='glauren_promo_seen';
        const today=new Date().toDateString();
        let seen=false; try{ seen=localStorage.getItem(KEY)===today; }catch(e){}
        function openPop(){ ov.classList.add('on'); try{localStorage.setItem(KEY,today);}catch(e){} }
        function closePop(){ ov.classList.remove('on'); }
        if(!seen) setTimeout(openPop, 6000);
        document.getElementById('popX').addEventListener('click',closePop);
        ov.addEventListener('click',e=>{ if(e.target===ov) closePop(); });
        document.getElementById('popForm').addEventListener('submit',async e=>{
            e.preventDefault();
            const name=document.getElementById('popName').value.trim(), phone=document.getElementById('popPhone').value.trim();
            if(!name||!phone) return;
            try{ await fetch('{{ route('contacto.popup') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({name,phone})}); }catch(err){}
            const msg=encodeURIComponent('¡Hola Glauren! Soy '+name+' y quiero que mi colegio sea el primero en llevarse la chaqueta o buso GRATIS.');
            window.open('https://wa.me/{{ $wa }}?text='+msg,'_blank');
            closePop();
        });
    })();
    const navEl=document.getElementById('siteHead');
    const onScroll=()=>navEl.classList.toggle('scrolled', window.scrollY>40);
    window.addEventListener('scroll', onScroll, {passive:true}); onScroll();
    // cerrar menú móvil al tocar un enlace
    document.querySelectorAll('#navLinks a').forEach(a=>a.addEventListener('click',()=>document.body.classList.remove('nav-open')));
    // Colegios: ver todos / ver menos (móvil)
    (function(){ var t=document.getElementById('logosToggle'), l=document.getElementById('trustedLogos'); if(!t||!l) return;
        t.addEventListener('click',function(){ var exp=l.classList.toggle('expanded'); l.classList.toggle('collapsed',!exp); t.textContent = exp ? 'Ver menos ↑' : 'Ver todos ('+l.children.length+') ↓'; }); })();
</script>

@if($types->contains(fn($t)=>$t->hasModel()))
<script type="importmap">
{ "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
}}
</script>
<script type="module">
    import * as THREE from 'three';
    import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
    import { KTX2Loader } from 'three/addons/loaders/KTX2Loader.js';
    import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';
    const viewers = []; let sharedEnv = null;
    function makeLoader(r){ const d=new DRACOLoader().setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/'); const k=new KTX2Loader().setTranscoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/basis/').detectSupport(r); const l=new GLTFLoader(); l.setDRACOLoader(d); l.setKTX2Loader(k); l.setMeshoptDecoder(MeshoptDecoder); return l; }
    function initViewer(box){
        const url=box.dataset.model; box.innerHTML='<div class="m3d-spin"><span class="spinner-border spinner-border-sm"></span></div>';
        const w=box.clientWidth||300, h=box.clientHeight||210;
        const renderer=new THREE.WebGLRenderer({antialias:true,alpha:true,powerPreference:'low-power'});
        renderer.setPixelRatio(Math.min(devicePixelRatio,1.6)); renderer.setSize(w,h,false); renderer.outputColorSpace=THREE.SRGBColorSpace;
        const scene=new THREE.Scene();
        if(!sharedEnv){ const p=new THREE.PMREMGenerator(renderer); sharedEnv=p.fromScene(new RoomEnvironment(),0.04).texture; }
        scene.environment=sharedEnv; scene.add(new THREE.HemisphereLight(0xffffff,0x333340,1.0));
        const key=new THREE.DirectionalLight(0xffffff,1.3); key.position.set(3,5,4); scene.add(key);
        const camera=new THREE.PerspectiveCamera(42,w/h,0.01,1000);
        const v={renderer,scene,camera,model:null,spin:true,box}; viewers.push(v);
        let down=false,lx=0;
        renderer.domElement.addEventListener('pointerdown',e=>{down=true;lx=e.clientX;v.spin=false;});
        window.addEventListener('pointerup',()=>down=false);
        window.addEventListener('pointermove',e=>{ if(down&&v.model){ v.model.rotation.y+=(e.clientX-lx)*0.01; lx=e.clientX; }});
        makeLoader(renderer).load(url,g=>{ const o=g.scene; const b=new THREE.Box3().setFromObject(o); const s=b.getSize(new THREE.Vector3()); const c=b.getCenter(new THREE.Vector3()); const m=Math.max(s.x,s.y,s.z)||1; o.scale.setScalar(2.6/m); o.position.sub(c.multiplyScalar(2.6/m)); scene.add(o); v.model=o; camera.position.set(0,0.2,3.4); box.innerHTML=''; box.appendChild(renderer.domElement); },undefined,err=>{ console.error(err); box.classList.add('mini3d--empty'); box.innerHTML=box.dataset.icon||'🧥'; });
    }
    const io=new IntersectionObserver((es)=>es.forEach(e=>{ e.target._vis=e.isIntersecting; if(e.isIntersecting&&!e.target._loaded){ e.target._loaded=true; initViewer(e.target); } }),{rootMargin:'200px'});
    document.querySelectorAll('.tcard .mini3d[data-model]').forEach(b=>io.observe(b));
    (function loop(){ requestAnimationFrame(loop); viewers.forEach(v=>{ if(!v.box._vis) return; if(v.model&&v.spin) v.model.rotation.y+=0.008; v.renderer.render(v.scene,v.camera); }); })();
</script>
@endif
</body>
</html>

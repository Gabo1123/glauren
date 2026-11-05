<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.6}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:1100px;margin:0 auto;padding:0 22px}
    nav.cn{display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:2px solid #000;background:rgba(13,13,15,.94);position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}
    .brand{display:flex;align-items:center;gap:10px}
    .brand .mk{width:38px;height:38px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:21px;border:2px solid #000;box-shadow:3px 3px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:21px;letter-spacing:.04em}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:18px;padding:12px 22px;border:2px solid #000;border-radius:10px;box-shadow:5px 5px 0 #000}
    .btn-gold{background:var(--gold);color:#1c1503}.btn-wa{background:var(--ok);color:#04240f}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--gold)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(40px,7vw,78px);line-height:.9;margin:10px 0 16px}
    h2{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:clamp(28px,4.5vw,46px);line-height:.95;margin:6px 0 20px}
    h3{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:21px;margin-bottom:4px}
    .gold{color:var(--gold)}
    header.hero{padding:48px 0 40px;border-bottom:2px solid #000;background:radial-gradient(760px 400px at 90% -10%,rgba(233,184,63,.18),transparent 60%)}
    .hero-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:36px;align-items:center}
    .lead{font-size:18px;color:#cfc9bd;margin-bottom:22px;max-width:580px}
    .cta{display:flex;gap:12px;flex-wrap:wrap}
    .pic img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:18px;border:3px solid #000;box-shadow:12px 12px 0 var(--gold)}
    section{padding:54px 0;border-bottom:1px solid var(--line)}
    .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    .card{background:var(--panel);border:2px solid #000;border-radius:14px;padding:18px;box-shadow:6px 6px 0 #000}
    .card p{color:var(--muted);font-size:14.5px}
    table{width:100%;border-collapse:collapse;background:var(--panel);border:2px solid #000;border-radius:14px;overflow:hidden}
    th,td{text-align:left;padding:12px 14px;border-bottom:1px solid var(--line);font-size:15px}
    th{background:#000;color:var(--gold);font-size:12px;letter-spacing:.12em;text-transform:uppercase}
    td.p{text-align:right;font-family:'Big Shoulders Display',sans-serif;font-weight:800;font-size:22px;white-space:nowrap}
    ol.steps{list-style:none;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;counter-reset:s}
    ol.steps li{background:var(--panel);border:2px solid #000;border-radius:14px;padding:16px;box-shadow:5px 5px 0 var(--gold);counter-increment:s}
    ol.steps li:before{content:counter(s);display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--gold);color:#1c1503;font-family:'Big Shoulders Display',sans-serif;font-weight:900;margin-bottom:8px}
    ol.steps b{display:block;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:18px}
    ol.steps span{color:var(--muted);font-size:13.5px}
    details{background:var(--panel);border:2px solid #000;border-radius:12px;padding:14px 16px;margin-bottom:10px}
    summary{cursor:pointer;font-weight:700;font-size:16.5px}
    details p{color:#cfc9bd;margin-top:8px}
    .cities{display:flex;flex-wrap:wrap;gap:8px}
    .cities a{border:1.5px solid var(--line);border-radius:999px;padding:7px 14px;font-size:14px}
    .cities a:hover{border-color:var(--gold);color:var(--gold)}
    .note{background:rgba(233,184,63,.1);border:1.5px solid rgba(233,184,63,.4);border-radius:12px;padding:14px 16px;color:#e8dcc0}
    footer{padding:34px 0;color:var(--muted);font-size:14px;text-align:center}
    @media(max-width:800px){.hero-grid,.grid3{grid-template-columns:1fr}ol.steps{grid-template-columns:1fr 1fr}.pic{display:none}}
</style>

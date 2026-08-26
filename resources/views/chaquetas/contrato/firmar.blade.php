@php use Illuminate\Support\Facades\Storage; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex">
<title>Firmar contrato · {{ $contract->code }} · Glauren</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;
        background-image:radial-gradient(900px 480px at 88% -10%,rgba(233,184,63,.14),transparent 60%)}
    a{text-decoration:none}
    .top{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:2px solid #000;background:var(--panel)}
    .brand{display:flex;align-items:center;gap:11px;color:var(--paper)}
    .brand .mk{width:38px;height:38px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:20px;border:2px solid #000;box-shadow:2px 2px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;letter-spacing:.04em}
    .code-tag{font-family:'Space Mono',monospace;font-size:12px;color:var(--gold);border:1.5px solid var(--gold);border-radius:6px;padding:5px 10px}
    .wrap{max-width:860px;margin:0 auto;padding:30px 22px 90px}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.18em;font-size:11px;color:var(--gold)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,6vw,52px);line-height:.86;margin:8px 0 4px}
    .sub{color:var(--muted);margin-bottom:20px}
    /* Documento tipo hoja */
    .doc{background:#fff;color:#1a1a1a;border:3px solid #000;border-radius:12px;padding:34px 38px;box-shadow:10px 10px 0 var(--gold);max-height:56vh;overflow:auto}
    .doc h3{font-size:21px;margin:0 0 14px;font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase}
    .doc h4{font-size:15px;margin:18px 0 6px;text-transform:uppercase;letter-spacing:.03em}
    .doc p{font-size:14px;line-height:1.65;margin:0 0 8px}
    .doc-hint{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);text-align:center;margin-top:8px}
    /* Firma */
    .card{background:var(--panel);border:3px solid #000;border-radius:12px;padding:24px;margin-top:24px;box-shadow:8px 8px 0 #000}
    .card h2{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:20px;color:var(--gold);margin:0 0 16px}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
    input[type=text]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);border-radius:8px;padding:12px;font-size:15px;margin-bottom:14px}
    input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.2)}
    #pad{width:100%;height:190px;background:#fff;border:2px solid #000;border-radius:10px;touch-action:none;cursor:crosshair}
    .padrow{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
    .btn{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:9px;padding:14px 26px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:18px;cursor:pointer;box-shadow:5px 5px 0 #000;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .btn-ghost{background:transparent;border:2px solid var(--line);color:var(--paper);border-radius:8px;padding:9px 15px;cursor:pointer;font-size:13px;font-family:'Space Mono',monospace}
    .btn-ghost:hover{border-color:var(--gold);color:var(--gold)}
    .err{background:rgba(255,74,61,.12);color:#ffb0a8;border:2px solid var(--hot);border-radius:8px;padding:11px 13px;font-size:13px;margin-bottom:14px}
    .signed{background:rgba(37,211,102,.1);border:3px solid var(--ok);border-radius:12px;padding:26px;margin-top:24px;text-align:center;box-shadow:8px 8px 0 #000}
    .signed .big{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:30px;color:var(--ok)}
    .signed img{max-height:120px;background:#fff;border:2px solid #000;border-radius:8px;padding:6px;margin-top:12px}
    .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:18px}
    @media print{
        body *{visibility:hidden}
        #printArea,#printArea *{visibility:visible}
        #printArea{position:absolute;left:0;top:0;width:100%}
        .doc{max-height:none;overflow:visible;box-shadow:none;border:1px solid #ccc}
        .no-print{display:none!important}
        body{background:#fff!important}
    }
</style>
</head>
<body>
<div class="top no-print">
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">Glauren</span></a>
    <span class="code-tag">{{ $contract->code }}</span>
</div>

<div class="wrap" id="printArea">
    <span class="ey">// Contrato de promoción · {{ $contract->school?->name }}</span>
    <h1>@if($contract->contract_status==='firmado')Contrato firmado @else Revisa y firma @endif</h1>
    <p class="sub no-print">Lee el contrato completo y fírmalo al final. Queda registrado con fecha y hora.</p>

    <div class="doc">{!! $contract->terms !!}</div>
    <div class="doc-hint no-print">↑ Desplázate dentro del documento para leerlo completo</div>

    @if($contract->contract_status === 'firmado')
        <div class="signed">
            <div class="big">✓ Contrato firmado</div>
            <p style="color:var(--muted);margin-top:8px">Firmado por <b style="color:var(--paper)">{{ $contract->signed_by_name }}</b> (CC {{ $contract->signed_by_document }})@if($contract->signed_at) el {{ $contract->signed_at->format('d/m/Y H:i') }}@endif.</p>
            @if($contract->signature_path)<img src="{{ Storage::disk('public')->url($contract->signature_path) }}" alt="Firma">@endif
            <div class="actions no-print">
                <button class="btn" onclick="window.print()">Descargar / Imprimir</button>
                <a href="{{ route('home') }}" class="btn-ghost">Ir al sitio</a>
            </div>
        </div>
    @else
        <div class="card no-print">
            <h2>Firmar el contrato</h2>
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('contrato.sign', $contract->sign_token) }}" id="signForm">
                @csrf
                <label>Nombre de quien firma *</label>
                <input type="text" name="signed_by_name" id="fname" value="{{ old('signed_by_name') }}" required>
                <label>Cédula *</label>
                <input type="text" name="signed_by_document" id="fdoc" value="{{ old('signed_by_document') }}" required>
                <label>Dibuja tu firma *</label>
                <canvas id="pad"></canvas>
                <div class="padrow">
                    <button type="button" class="btn-ghost" id="clearPad">Limpiar firma</button>
                    <span style="font-family:'Space Mono',monospace;font-size:11px;color:var(--muted)">Usa el mouse o el dedo</span>
                </div>
                <input type="hidden" name="signature" id="sig">
                <div style="margin-top:18px"><button type="submit" class="btn">Firmar contrato →</button></div>
            </form>
        </div>
    @endif
</div>

@if($contract->contract_status !== 'firmado')
<script>
    const canvas=document.getElementById('pad'), ctx=canvas.getContext('2d');
    function resize(){ const r=canvas.getBoundingClientRect(); canvas.width=r.width; canvas.height=r.height; ctx.lineWidth=2.8; ctx.lineCap='round'; ctx.strokeStyle='#111'; }
    resize(); window.addEventListener('resize', resize);
    let drawing=false, dirty=false, last=null;
    function pos(e){ const r=canvas.getBoundingClientRect(); const t=e.touches?e.touches[0]:e; return {x:t.clientX-r.left, y:t.clientY-r.top}; }
    function start(e){ drawing=true; last=pos(e); e.preventDefault(); }
    function move(e){ if(!drawing) return; const p=pos(e); ctx.beginPath(); ctx.moveTo(last.x,last.y); ctx.lineTo(p.x,p.y); ctx.stroke(); last=p; dirty=true; e.preventDefault(); }
    function end(){ drawing=false; }
    canvas.addEventListener('pointerdown',start); canvas.addEventListener('pointermove',move);
    window.addEventListener('pointerup',end);
    document.getElementById('clearPad').addEventListener('click',()=>{ ctx.clearRect(0,0,canvas.width,canvas.height); dirty=false; });
    document.getElementById('signForm').addEventListener('submit',(e)=>{
        if(!dirty){ e.preventDefault(); alert('Por favor dibuja tu firma.'); return; }
        document.getElementById('sig').value=canvas.toDataURL('image/png');
    });
</script>
@endif
</body>
</html>

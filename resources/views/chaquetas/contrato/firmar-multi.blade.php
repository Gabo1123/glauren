@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Firmar contrato · {{ $brand }}</title><meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;min-height:100vh;padding:24px;background-image:radial-gradient(900px 460px at 80% -10%,rgba(233,184,63,.14),transparent 60%)}
    .wrap{max-width:560px;margin:0 auto}
    .brand{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;color:var(--gold);font-size:24px;text-align:center;margin-bottom:6px}
    .card{background:var(--panel);border:2px solid #000;border-radius:16px;padding:28px 26px;box-shadow:8px 8px 0 var(--gold);margin-top:18px}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:26px;margin-bottom:6px}
    p.sub{color:var(--muted);font-size:14px;margin-bottom:18px}
    .kv{font-size:14px;color:#cfc9bd;margin-bottom:8px}.kv b{color:var(--paper)}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin:12px 0 6px;text-transform:uppercase}
    input[type=text]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:12px;border-radius:8px;font-size:15px}
    input.pin{text-align:center;letter-spacing:.4em;font-size:26px;font-family:'Space Mono',monospace}
    input:focus{outline:none;border-color:var(--gold)}
    .btn{width:100%;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:14px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:18px;cursor:pointer;box-shadow:5px 5px 0 #000;margin-top:14px}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .btn-ghost{background:none;border:1px solid var(--line);color:var(--muted);border-radius:8px;padding:8px 14px;cursor:pointer;font-size:13px}
    .ok{background:rgba(37,211,102,.12);border:2px solid var(--ok);color:#bff5d0;border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:14px}
    .err{background:rgba(255,74,61,.12);border:2px solid var(--hot);color:#ffb0a8;border-radius:10px;padding:10px 13px;margin-bottom:14px;font-size:13px}
    #pad{width:100%;height:180px;background:#fff;border:2px solid #000;border-radius:10px;touch-action:none;cursor:crosshair}
    .padrow{display:flex;justify-content:space-between;align-items:center;margin-top:8px}
    .terms{max-height:200px;overflow-y:auto;background:var(--panel2);border:1px solid var(--line);border-radius:10px;padding:14px;font-size:13px;color:#cfc9bd;line-height:1.6;margin-bottom:8px}
    .signers{margin-top:14px;border-top:1px solid var(--line);padding-top:14px}
    .signer-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:6px 0}
    .badge-ok{color:var(--ok)}.badge-pend{color:var(--muted)}
</style>
</head>
<body>
<div class="wrap">
    <div class="brand">{{ $brand }}</div>

    @if($signer->hasSigned())
        {{-- Ya firmó --}}
        <div class="card" style="text-align:center">
            <div style="font-size:48px">✅</div>
            <h1>¡Firma registrada!</h1>
            <p class="sub">Gracias, {{ $signer->name }}. Tu firma del contrato <b>{{ $contract->code }}</b> quedó guardada.</p>
            @if($contract->allSigned())
                <div class="ok">🎉 ¡Todos los acudientes firmaron! El contrato está completo.</div>
            @else
                <div class="kv">Faltan otros acudientes por firmar. Cada uno recibe su propio enlace.</div>
            @endif
        </div>
    @elseif($step === 'verify')
        {{-- Verificar identidad con código --}}
        <div class="card">
            <h1>Verifica tu identidad</h1>
            <p class="sub">Hola {{ $signer->name }}, para firmar el contrato <b>{{ $contract->code }}</b> te enviaremos un código a tu WhatsApp.</p>
            @if(session('ok'))<div class="ok"><i>✅</i> {{ session('ok') }}</div>@endif
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif

            @if(!session('ok'))
                <form method="POST" action="{{ route('firmar.codigo', $signer->token) }}">@csrf
                    <button class="btn">📲 Enviarme el código por WhatsApp</button>
                </form>
            @else
                <form method="POST" action="{{ route('firmar.verificar', $signer->token) }}">@csrf
                    <label>Escribe el código (6 números)</label>
                    <input type="text" name="code" class="pin" inputmode="numeric" maxlength="6" required autofocus placeholder="••••••">
                    <button class="btn">Verificar →</button>
                </form>
                <form method="POST" action="{{ route('firmar.codigo', $signer->token) }}" style="margin-top:10px;text-align:center">@csrf
                    <button type="submit" class="btn-ghost">↻ Reenviar código</button>
                </form>
            @endif
        </div>
    @else
        {{-- Firmar --}}
        <div class="card">
            <h1>Firmar contrato</h1>
            <p class="sub">Contrato <b>{{ $contract->code }}</b> — {{ $contract->school?->name }} · {{ $contract->jacketType?->name ?? 'Chaqueta' }} · Prom {{ $contract->year }}</p>
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif

            <label>Contrato completo</label>
            <div class="terms">{!! nl2br(e($contract->renderedContract())) !!}</div>

            <form method="POST" action="{{ route('firmar.guardar', $signer->token) }}" id="signForm">@csrf
                <label>Tu nombre</label>
                <input type="text" value="{{ $signer->name }}" disabled style="opacity:.8">
                <label>Tu documento</label>
                <input type="text" name="signed_document" required placeholder="Tu número de documento">
                <label>Tu firma (dibuja con el dedo o el mouse)</label>
                <canvas id="pad"></canvas>
                <div class="padrow">
                    <button type="button" class="btn-ghost" id="clearPad">Limpiar firma</button>
                    <span style="color:var(--muted);font-size:12px">Firma dentro del recuadro blanco</span>
                </div>
                <input type="hidden" name="signature" id="sig">
                <button class="btn">✍️ Firmar contrato</button>
            </form>

            {{-- Estado de firmantes --}}
            @if($contract->signers->count() > 1)
            <div class="signers">
                <label>Acudientes de este contrato</label>
                @foreach($contract->signers as $sg)
                    <div class="signer-row">
                        <span>{{ $sg->name }}@if($sg->role) <span style="color:var(--muted)">· {{ $sg->role }}</span>@endif</span>
                        <span class="{{ $sg->hasSigned() ? 'badge-ok' : 'badge-pend' }}">{{ $sg->hasSigned() ? '✓ Firmó' : 'Pendiente' }}</span>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    @endif
</div>

@if(!$signer->hasSigned() && $step === 'sign')
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

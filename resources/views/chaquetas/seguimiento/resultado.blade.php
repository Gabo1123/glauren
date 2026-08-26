@php
    use App\Modules\Chaquetas\Models\Contract;
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
    $c = $student->contract;
    $stages = Contract::STAGES;
    $total = count($stages);
    $idx = $c?->stageIndex() ?? 0;
    $pct = (int) round((($idx + 1) / $total) * 100);

    // Pagos: métodos habilitados (global) + plan de abonos
    $allow = [
        'efectivo'    => (Setting::get('pay_allow_efectivo', '0') === '1'),
        'nequi'       => (Setting::get('pay_allow_nequi', '1') === '1'),
        'bancolombia' => (Setting::get('pay_allow_bancolombia', '1') === '1'),
    ];
    $anyMethod = $allow['efectivo'] || $allow['nequi'] || $allow['bancolombia'];
    $payTotal  = (float) ($student->amount ?? $c?->price_per_unit ?? 0);
    $initPct   = (int) Setting::get('pay_initial_pct', 0);
    $finalPct  = (int) Setting::get('pay_final_pct', 50);
    $finalAmt  = round($payTotal * $finalPct / 100);

    // Fecha estimada de entrega (~35 días hábiles desde la firma o creación del contrato)
    $prodDays  = (int) Setting::get('production_days', 35);
    $startDate = $c?->signed_at ?? $c?->created_at;
    $eta       = $startDate ? $startDate->copy()->addWeekdays($prodDays) : null;
    $delivered = $c?->production_status === 'entregado';
    $isReady   = $c?->production_status === 'listo';

    // Estado de pago (etiqueta corta para el resumen)
    $payState  = $student->isPaid() ? ['✓ Pagado','ok'] : ($student->isUnderReview() ? ['⏳ En revisión','rev'] : ['Pendiente','pend']);

    // ¿Qué sigue? mensaje según la etapa
    $nextStep = match($c?->production_status) {
        'cotizado'        => ['📝','Tu curso está definiendo el contrato. Pronto arranca todo.'],
        'aprobado'        => ['✅','¡Contrato aprobado! Sigue la toma de medidas.'],
        'toma_medidas'    => ['📏', $student->hasMeasurements() ? 'Ya tenemos tus medidas. ¡Vamos a producción!' : 'Faltan tus medidas. Se toman en esta etapa — atento a tu curso.'],
        'en_produccion'   => ['🧵','Estamos fabricando tu prenda. Te mostramos fotos del avance aquí.'],
        'control_calidad' => ['🔍','Revisión final de calidad antes de entregar.'],
        'listo'           => ['🎉','¡Tu prenda está lista! Coordina la entrega y deja tu pago al día.'],
        'entregado'       => ['📦','¡Entregada! Gracias por confiar en nosotros 💛'],
        default           => ['✨','Estamos con tu pedido. Cualquier duda, escríbenos.'],
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi chaqueta · {{ $student->name }}</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--blast:#e9b83f;--blast2:#c9922e;--gold:#e9b83f;--hot:#ff4a3d;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;padding-bottom:40px}
    a{text-decoration:none}
    .top{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:2px solid #000;background:var(--panel)}
    .brand{display:flex;align-items:center;gap:11px;color:var(--paper)}
    .brand .mk{width:38px;height:38px;border-radius:9px;background:var(--gold);color:#1c1503;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:20px;border:2px solid #000;box-shadow:2px 2px 0 #000}
    .brand .wm{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;letter-spacing:.04em}
    .back-top{font-family:'Space Mono',monospace;font-size:13px;color:var(--muted)} .back-top:hover{color:var(--gold)}
    .wrap{max-width:860px;margin:0 auto;padding:0 22px}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:11px;color:var(--blast2)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(38px,7vw,64px);line-height:.85;margin:8px 0 6px}
    .lead{color:var(--muted)}
    .lead b{color:var(--paper)}
    .card{background:var(--panel);border:2px solid #000;border-radius:12px;padding:24px;margin-top:20px;box-shadow:6px 6px 0 #000}
    .card h2{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:.02em;font-size:19px;margin-bottom:16px}
    /* progreso */
    .prog-head{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;flex-wrap:wrap;gap:8px}
    .prog-now{font-family:'Space Mono',monospace;font-size:13px;color:var(--muted)}
    .prog-now b{color:var(--gold)}
    .pct{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:40px;color:var(--gold);line-height:.8}
    .bar{height:14px;background:var(--panel2);border:2px solid #000;border-radius:20px;overflow:hidden}
    .bar > i{display:block;height:100%;width:{{ $pct }}%;background:repeating-linear-gradient(45deg,var(--blast),var(--blast) 10px,var(--blast2) 10px,var(--blast2) 20px)}
    /* timeline */
    .tl{margin-top:22px;display:flex;flex-direction:column}
    .step{display:flex;gap:14px;align-items:flex-start;position:relative;padding-bottom:18px}
    .step:not(:last-child)::before{content:'';position:absolute;left:14px;top:30px;bottom:0;width:3px;background:var(--line)}
    .step.done:not(:last-child)::before{background:var(--gold)}
    .dot{width:30px;height:30px;border-radius:8px;border:2px solid #000;display:grid;place-items:center;flex-shrink:0;font-family:'Big Shoulders Display',sans-serif;font-weight:800;font-size:14px;color:var(--muted);background:var(--panel2);z-index:1}
    .step.done .dot{background:var(--gold);color:#1c1503}
    .step.current .dot{background:var(--gold);color:#1c1503;border-color:#000;box-shadow:0 0 0 3px rgba(233,184,63,.3)}
    .step .lbl{font-family:'Big Shoulders Display',sans-serif;font-weight:700;text-transform:uppercase;font-size:18px;padding-top:4px}
    .step.current .lbl{color:var(--gold)}
    .step.pending .lbl{color:var(--muted)}
    .step .now-tag{font-family:'Space Mono',monospace;font-size:10px;background:#0d0d0f;color:var(--gold);padding:2px 7px;border-radius:3px;border:1.5px solid var(--gold);margin-left:8px;vertical-align:middle}
    /* fotos */
    .ph-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px}
    .ph-grid a{position:relative;display:block;border:2px solid #000;border-radius:8px;overflow:hidden;box-shadow:3px 3px 0 #000}
    .ph-grid img{width:100%;aspect-ratio:1;object-fit:cover;display:block}
    .tag{position:absolute;top:6px;left:6px;font-family:'Space Mono',monospace;font-size:10px;padding:2px 7px;border-radius:3px;border:1.5px solid #000;font-weight:700}
    .tag.ok{background:var(--ok);color:#04240f}
    /* pago */
    .pay{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .badge{font-family:'Space Mono',monospace;padding:6px 12px;border-radius:5px;font-size:13px;font-weight:700;border:2px solid #000}
    .badge.ok{background:var(--ok);color:#04240f}
    .badge.pend{background:var(--gold);color:#1c1503}
    .big-price{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:30px;color:var(--paper);margin-top:8px}
    .btn{background:var(--ok);color:#04240f;border:2px solid #000;border-radius:8px;padding:13px 22px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:17px;cursor:pointer;box-shadow:4px 4px 0 #000;display:inline-flex;gap:8px;align-items:center;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .note{font-family:'Space Mono',monospace;font-size:12px;color:var(--muted);margin-top:10px}
    .badge.rev{background:#e0a422;color:#1c1503}
    .note.ok-note{color:var(--ok);font-size:13px} .note.err-note{color:var(--hot);font-size:13px}
    .paybox{margin-top:18px;border-top:2px dashed var(--line);padding-top:16px}
    .paybox-t{font-size:14px;color:var(--paper);font-weight:600;margin-bottom:10px}
    .methods{display:flex;flex-direction:column;gap:8px;margin-bottom:10px}
    .methods label{display:flex;align-items:center;gap:10px;background:var(--panel2);border:2px solid #000;border-radius:8px;padding:11px 14px;cursor:pointer;font-size:14px;transition:.12s}
    .methods label:hover{border-color:var(--gold)}
    .methods input{width:18px;height:18px;accent-color:var(--gold)}
    .acct{display:none;background:rgba(233,184,63,.12);border:1px solid var(--gold);border-radius:8px;padding:10px 12px;font-size:13px;margin-bottom:10px}
    .acct.show{display:block}
    .proof{margin-bottom:12px}
    .proof label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px}
    .proof input{width:100%;background:var(--panel2);border:2px solid #000;border-radius:8px;padding:9px;color:var(--paper);font-size:13px}
    .btn2{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:12px 20px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:16px;cursor:pointer;box-shadow:4px 4px 0 #000;width:100%}
    .btn2:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    /* datos */
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .kv{background:var(--panel2);border:2px solid #000;border-radius:8px;padding:11px 13px}
    .kv .k{font-family:'Space Mono',monospace;font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em}
    .kv .v{font-size:17px;font-weight:600}
    .backrow{display:flex;gap:16px;flex-wrap:wrap;margin-top:24px;align-items:center}
    .back{font-family:'Space Mono',monospace;color:var(--muted);font-size:13px} .back:hover{color:var(--gold)}
    .help{color:#25d366;font-size:13px;font-family:'Space Mono',monospace}
    /* Resumen rápido (chips) */
    .chips{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-top:20px}
    .chip{display:flex;align-items:center;gap:11px;background:var(--panel);border:2px solid #000;border-radius:12px;padding:14px 16px;box-shadow:4px 4px 0 #000}
    .chip .ci{font-size:22px;flex:none}
    .chip .ck{display:block;font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
    .chip .cv{display:block;font-family:'Big Shoulders Display',sans-serif;font-weight:800;font-size:18px;line-height:1.1;text-transform:uppercase}
    /* ¿Qué sigue? */
    .next{display:flex;align-items:center;gap:16px;border-color:var(--gold);box-shadow:6px 6px 0 var(--gold)}
    .next-em{font-size:34px;flex:none}
    .next-t{font-family:'Space Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--gold)}
    .next-d{font-size:16px;margin-top:2px}
    @media(max-width:640px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="top">
    <a href="{{ route('home') }}" class="brand"><span class="mk">G</span><span class="wm">Glauren</span></a>
    <a href="{{ route('seguimiento') }}" class="back-top">← Buscar otro</a>
</div>

<div class="wrap">
    <div style="padding-top:28px">
        <span class="ey">// {{ $c?->school?->name ?? 'Tu contrato' }} · Prom {{ $c?->year }}</span>
        <h1>¡Hola,<br>{{ $student->nickname ?: $student->name }}!</h1>
        <p class="lead">Estás dentro del contrato <b>{{ $c?->code }}</b> — {{ $c?->jacketType?->name ?? 'Chaqueta' }}. Aquí va todo tu proceso.</p>
    </div>

    {{-- Resumen rápido --}}
    <div class="chips">
        <div class="chip"><span class="ci">🏷️</span><div><span class="ck">Prenda</span><span class="cv">{{ $c?->jacketType?->name ?? 'Chaqueta' }}</span></div></div>
        <div class="chip"><span class="ci">📍</span><div><span class="ck">Etapa</span><span class="cv">{{ $c?->stageLabel() ?? '—' }}</span></div></div>
        @if($eta && !$delivered)
        <div class="chip"><span class="ci">🚚</span><div><span class="ck">Entrega estimada</span><span class="cv">{{ $eta->locale('es')->isoFormat('D MMM YYYY') }}</span></div></div>
        @elseif($delivered)
        <div class="chip"><span class="ci">📦</span><div><span class="ck">Entrega</span><span class="cv">Entregada ✓</span></div></div>
        @endif
        <div class="chip"><span class="ci">💳</span><div><span class="ck">Pago</span><span class="cv">{{ $payState[0] }}</span></div></div>
    </div>

    {{-- ¿Qué sigue? --}}
    <div class="card next">
        <span class="next-em">{{ $nextStep[0] }}</span>
        <div><div class="next-t">¿Qué sigue?</div><div class="next-d">{{ $nextStep[1] }}</div></div>
    </div>

    {{-- Progreso --}}
    <div class="card">
        <div class="prog-head">
            <div>
                <h2 style="margin:0">Avance de fabricación</h2>
                <div class="prog-now">Vas en <b>{{ $c?->stageLabel() ?? '—' }}</b> · etapa {{ $idx + 1 }} de {{ $total }}</div>
            </div>
            <div class="pct">{{ $pct }}%</div>
        </div>
        <div class="bar"><i></i></div>

        <div class="tl">
            @foreach($stages as $key => $label)
                @php $i = array_search($key, array_keys($stages), true);
                     $cls = $i < $idx ? 'done' : ($i === $idx ? 'current done' : 'pending'); @endphp
                <div class="step {{ $cls }}">
                    <div class="dot">{{ $i < $idx ? '✓' : $i + 1 }}</div>
                    <div class="lbl">{{ $label }}@if($i === $idx)<span class="now-tag">AQUÍ VAS</span>@endif</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Avisos por WhatsApp --}}
    <div class="card">
        <h2>🔔 Avisos por WhatsApp</h2>
        @if(!empty($phoneSaved))
            <div class="note ok-note">✅ ¡Listo! Te avisaremos por WhatsApp en cada avance de tu pedido.</div>
        @endif
        @if($student->phone)
            <div class="note">Recibirás novedades de tu {{ $c?->jacketType?->name ?? 'chaqueta' }} en el WhatsApp <b>+{{ $student->phone }}</b> 📲</div>
            <details style="margin-top:10px">
                <summary style="cursor:pointer;color:var(--muted);font-size:13px">Cambiar número</summary>
                @include('chaquetas.seguimiento._phone-form')
            </details>
        @else
            <p class="lead" style="margin:0 0 12px">Déjanos tu número y te avisamos <b>solos</b> cuando tu pedido cambie de etapa, esté listo y cuando subamos fotos. 🚀</p>
            @include('chaquetas.seguimiento._phone-form')
        @endif
    </div>

    {{-- Fotos --}}
    @if($c && $c->photos->count())
    <div class="card">
        <h2>📸 Fotos de tu producción</h2>
        <div class="ph-grid">
            @foreach($c->photos as $ph)
                <a href="{{ $ph->url() }}" target="_blank" rel="noopener">
                    <img src="{{ $ph->url() }}" alt="{{ $ph->caption ?: 'Foto de la producción de tu chaqueta de prom · Glauren' }}" loading="lazy">
                    @if($ph->phase === 'final')<span class="tag ok">Final</span>@endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Pago --}}
    <div class="card">
        <h2>Tu pago</h2>

        @if($student->isPaid())
            <span class="badge ok">✓ Pagado</span>
            <div class="note">Recibimos y aprobamos tu pago. ¡Gracias! 🎉</div>
        @elseif($student->isUnderReview())
            <span class="badge rev">⏳ En revisión</span>
            <div class="note">Registramos tu pago por <b>{{ $student->methodLabel() }}</b>. Un asesor lo revisará y lo aprobará pronto. Si tienes dudas, escríbenos por WhatsApp.</div>
        @else
            @if(!empty($declared))
                <div class="note ok-note">✅ ¡Listo! Registramos tu pago. Queda <b>pendiente por aprobación</b> — te confirmaremos apenas lo revisemos.</div>
            @endif
            @if(!empty($declareErrors))
                <div class="note err-note">@foreach($declareErrors as $e){{ $e }}<br>@endforeach</div>
            @endif

            <div class="pay">
                <div>
                    <span class="badge pend">Pago pendiente</span>
                    <div class="big-price">${{ number_format($finalAmt, 0, ',', '.') }}</div>
                    <div class="note">Plan: <b>{{ $initPct }}%</b> al inicio (nada para empezar) · <b>{{ $finalPct }}%</b> el día de la entrega.</div>
                </div>
                <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('pay.start', ['student' => $student->id]) }}" class="btn">Pagar en línea →</a>
            </div>

            @if($anyMethod)
            <div class="paybox">
                <div class="paybox-t">O registra tu pago aquí:</div>
                <form method="POST" action="{{ route('seguimiento.pay') }}" enctype="multipart/form-data" id="payForm">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="document" value="{{ $student->document }}">
                    <div class="methods">
                        @if($allow['efectivo'])<label><input type="radio" name="method" value="efectivo"> 💵 Efectivo (el día de la entrega)</label>@endif
                        @if($allow['nequi'])<label><input type="radio" name="method" value="nequi"> 📲 Nequi</label>@endif
                        @if($allow['bancolombia'])<label><input type="radio" name="method" value="bancolombia"> 🏦 Bancolombia</label>@endif
                    </div>
                    @php $nequi = Setting::get('pay_nequi'); $banco = Setting::get('pay_bancolombia'); @endphp
                    @if($allow['nequi'] && $nequi)<div class="acct" data-m="nequi">Envía a <b>Nequi:</b> {{ $nequi }}</div>@endif
                    @if($allow['bancolombia'] && $banco)<div class="acct" data-m="bancolombia">Consigna a <b>Bancolombia:</b> {{ $banco }}</div>@endif
                    <div class="proof" id="proofBox" style="display:none">
                        <label>Sube la foto del comprobante (captura o foto)</label>
                        <input type="file" name="proof" accept="image/*">
                    </div>
                    <button class="btn2" type="submit">Registrar mi pago</button>
                    @if($allow['efectivo'])<div class="note">Con efectivo no necesitas comprobante: lo confirmamos el día de la entrega.</div>@endif
                </form>
            </div>
            @endif
        @endif
    </div>

    {{-- Datos --}}
    <div class="card">
        <h2>Tus datos</h2>
        <div class="grid">
            <div class="kv"><div class="k">Nombre</div><div class="v">{{ $student->name }}</div></div>
            <div class="kv"><div class="k">Documento</div><div class="v">{{ $student->document }}</div></div>
            @if($student->hasMeasurements())
                <div class="kv"><div class="k">Talla</div><div class="v">{{ $student->size ?: '—' }}</div></div>
                <div class="kv"><div class="k">Contorno de pecho</div><div class="v">{{ $student->chest ? $student->chest.' cm' : '—' }}</div></div>
                <div class="kv"><div class="k">Contorno de cintura</div><div class="v">{{ $student->waist ? $student->waist.' cm' : '—' }}</div></div>
                <div class="kv"><div class="k">Contorno de cadera</div><div class="v">{{ $student->hip ? $student->hip.' cm' : '—' }}</div></div>
                <div class="kv"><div class="k">Largo de manga</div><div class="v">{{ $student->sleeve_length ? $student->sleeve_length.' cm' : '—' }}</div></div>
                <div class="kv"><div class="k">Largo de talle</div><div class="v">{{ $student->talle_length ? $student->talle_length.' cm' : '—' }}</div></div>
            @else
                <div class="kv" style="grid-column:1/-1"><div class="k">Medidas</div><div class="v" style="font-size:14px;font-weight:400">Aún no registran tus medidas. Se toman en la etapa de <b>Toma de medidas</b>.</div></div>
            @endif
        </div>
    </div>

    {{-- Modelo 3D de tu prenda --}}
    @if($c?->jacketType?->hasModel())
    <div class="card">
        <h2>🧊 Mira tu modelo en 3D</h2>
        <p class="lead" style="margin:0 0 14px">Explora el estilo <b>{{ $c->jacketType->name }}</b> en 3D: gíralo y míralo por todos lados.</p>
        <a href="{{ route('design', $c->jacketType->slug) }}" class="btn" style="text-decoration:none">Ver en 3D →</a>
    </div>
    @endif

    {{-- Garantía (cuando ya fue entregada) --}}
    @if($delivered)
    <div class="card">
        <h2>🛡️ Tu garantía</h2>
        <div class="note">Tu prenda tiene <b>garantía durante el año escolar</b> por defectos de fabricación (confección o cremalleras). No cubre mal uso ni lavado inadecuado. Revisa la etiqueta con las instrucciones de lavado. ¿Algún problema? Escríbenos por WhatsApp.</div>
    </div>
    @endif

    {{-- Portal del estudiante --}}
    <div class="card">
        <h2>🎓 Tu portal del estudiante</h2>
        <p class="lead" style="margin:0 0 14px">Arma tu <b>perfil</b>, sube tus <b>fotos</b> y comparte en el <b>muro de tu curso</b> con tus compañeros.</p>
        <a href="{{ route('student.login') }}" class="btn" style="text-decoration:none">Entrar a mi portal →</a>
    </div>

    <div class="backrow">
        <a href="{{ route('seguimiento') }}" class="back">← Buscar otro documento</a>
        <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, tengo una duda sobre mi chaqueta (contrato '.($c?->code ?? '').').') }}" target="_blank" rel="noopener" class="help">¿Dudas? Escríbenos por WhatsApp</a>
    </div>
</div>
<script>
(function(){
    var form=document.getElementById('payForm'); if(!form) return;
    var proof=document.getElementById('proofBox');
    var accts=form.querySelectorAll('.acct');
    form.querySelectorAll('input[name="method"]').forEach(function(r){
        r.addEventListener('change',function(){
            var m=this.value, transfer=(m==='nequi'||m==='bancolombia');
            proof.style.display = transfer ? 'block' : 'none';
            accts.forEach(function(a){ a.classList.toggle('show', a.dataset.m===m); });
        });
    });
})();
</script>
</body>
</html>

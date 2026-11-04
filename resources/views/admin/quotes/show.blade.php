@extends('layouts.admin')
@section('title', 'Cotización '.$quote->code())

@php
    use App\Modules\Chaquetas\Models\Quote;
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $phoneDigits = $quote->whatsappPhone();
    $waChat = $phoneDigits ? 'https://wa.me/'.$phoneDigits : null;
    $tel = preg_replace('/[^\d+]/', '', (string) $quote->customer_phone);
    $lost = $quote->status === 'cancelado';
    $current = collect($journey)->firstWhere('current', true);
    $keys = array_keys(Quote::JOURNEY);
    $nextKey = $current ? ($keys[array_search($current['key'], $keys, true) + 1] ?? null) : null;
    $label = fn ($k) => Quote::JOURNEY[$k][0] ?? $k;
    $phases = ['venta' => 'Venta', 'cierre' => 'Cierre', 'produccion' => 'Producción y entrega'];
    $unit = $summary['unit_total'] ?: ($quote->quantity ? $quote->estimated_price / max(1, $quote->quantity) : 0);
    $biz = $quote->isBusiness();
    $custom = $quote->isCustom();
    $editUrl = $quote->customEditUrl();
    $bizTotals = $custom ? $quote->businessTotals() : null;
    $title = $biz ? ($quote->school ?: $quote->customer_name) : $quote->customer_name;
    $initial = mb_strtoupper(mb_substr(trim($title) ?: '?', 0, 1));
@endphp

@section('content')
<style>
.qx-hero{display:flex;gap:20px;align-items:center;flex-wrap:wrap}
.qx-av{flex:0 0 64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#FFC72C,#FF6B1A);color:#0E0E0E;display:grid;place-items:center;font-family:var(--g-font-display);font-size:34px;font-weight:800}
.qx-name{font-family:var(--g-font-display);text-transform:uppercase;font-size:30px;line-height:1;margin:0}
.qx-sub{color:var(--g-muted);font-size:13px;margin-top:6px;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.qx-act{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.qx-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:999px;border:1px solid var(--g-line);color:var(--g-bone);text-decoration:none;font-size:13px;font-weight:600;background:var(--g-surface-2);transition:var(--g-t)}
.qx-btn:hover{border-color:var(--g-gold);color:var(--g-gold)}
.qx-btn.wa{background:#25d366;border-color:#25d366;color:#04240f}
.qx-btn.wa:hover{filter:brightness(1.08);color:#04240f}
.qx-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;margin-top:20px}
.qx-kpi{background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:12px;padding:12px 14px}
.qx-kpi small{display:block;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--g-muted)}
.qx-kpi b{display:block;font-size:20px;margin-top:2px;line-height:1.15}
.qx-kpi .hot{color:#FFC72C}

.qx-steps{display:flex;gap:14px;overflow-x:auto;padding:4px 2px 10px}
.qx-phase{flex:var(--n) 1 0;min-width:calc(var(--n) * 68px)}
.qx-phase>small{display:block;font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--g-muted);margin-bottom:10px;padding-left:2px}
.qx-row{display:flex}
.qx-step{position:relative;flex:1 1 0;min-width:66px;text-align:center;padding:0 4px;color:var(--g-muted);margin:0}
.qx-step button{background:none;border:0;padding:0;width:100%;color:inherit;font:inherit;cursor:pointer}
.qx-step:before{content:"";position:absolute;top:20px;left:-50%;width:100%;height:3px;background:var(--g-surface-3);z-index:0}
.qx-row .qx-step:first-child:before{display:none}
.qx-step.done:before,.qx-step.current:before{background:#FFC72C}
.qx-dot{position:relative;z-index:1;width:42px;height:42px;margin:0 auto 8px;border-radius:50%;display:grid;place-items:center;background:var(--g-surface-3);border:2px solid var(--g-line);font-size:17px;transition:var(--g-t)}
.qx-step:hover .qx-dot{border-color:#FFC72C;color:#FFC72C}
.qx-step.done{color:var(--g-bone)}
.qx-step.done .qx-dot{background:#FFC72C;border-color:#FFC72C;color:#0E0E0E}
.qx-step.current{color:#fff;font-weight:700}
.qx-step.current .qx-dot{background:#FF6B1A;border-color:#FF6B1A;color:#0E0E0E;box-shadow:0 0 0 6px rgba(255,107,26,.22);animation:qxp 1.8s infinite}
@keyframes qxp{50%{box-shadow:0 0 0 11px rgba(255,107,26,.06)}}
.qx-step span{display:block;font-size:12px;line-height:1.25}
.qx-lost .qx-step{opacity:.45}

.qx-now{margin-top:16px;border-radius:14px;padding:18px 20px;background:linear-gradient(90deg,rgba(255,199,44,.14),rgba(255,107,26,.06));border:1px solid rgba(255,199,44,.4);display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.qx-now .ic{flex:0 0 46px;height:46px;border-radius:12px;background:#FFC72C;color:#0E0E0E;display:grid;place-items:center;font-size:22px}
.qx-now h6{margin:0 0 3px;font-size:16px}
.qx-now p{margin:0;font-size:13px;color:var(--g-muted)}
.qx-now .go{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.qx-cta{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:999px;padding:10px 18px;font-weight:700;font-size:14px;background:#FFC72C;color:#0E0E0E;text-decoration:none}
.qx-cta:hover{background:#ffd34f;color:#0E0E0E}
.qx-ghost{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--g-line);border-radius:999px;padding:9px 15px;font-size:13px;font-weight:600;background:transparent;color:var(--g-bone);text-decoration:none}
.qx-ghost:hover{border-color:var(--g-danger);color:var(--g-danger)}

.qx-card h6.t{display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:15px}
.qx-num{display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#FFC72C;color:#0E0E0E;font-size:12px;font-weight:800;flex:0 0 24px}
.qx-tl{position:relative;padding-left:4px}
.qx-tl .it{display:flex;gap:12px;padding-bottom:14px;position:relative}
.qx-tl .it:not(:last-child):after{content:"";position:absolute;left:15px;top:34px;bottom:2px;width:2px;background:var(--g-line)}
.qx-tl .b{flex:0 0 32px;height:32px;border-radius:50%;display:grid;place-items:center}
.qx-chips{display:flex;gap:6px;flex-wrap:wrap;margin:-4px 0 12px}
.qx-chips button{border:1px solid var(--g-line);background:var(--g-surface-2);color:var(--g-bone);border-radius:999px;font-size:12px;padding:4px 10px}
.qx-chips button:hover{border-color:#FFC72C;color:#FFC72C}
.qx-sel td{padding:9px 0;border-color:var(--g-line)!important}
.qx-sel tr.tot td{font-weight:800;font-size:16px;color:#FFC72C}
.qx-warn{background:rgba(224,164,34,.12);border:1px solid rgba(224,164,34,.4);color:var(--g-warn);border-radius:10px;padding:10px 16px;margin-bottom:16px}
@media (max-width:992px){.qx-kpis{grid-template-columns:repeat(2,1fr)}.qx-act{margin-left:0}}
</style>

<a href="{{ route('admin.quotes.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Cotizaciones</a>

@if(session('warn'))<div class="qx-warn mt-3"><i class="bi bi-exclamation-triangle"></i> {{ session('warn') }}</div>@endif
@if($errors->any())<div class="qx-warn mt-3">{{ $errors->first() }}</div>@endif

{{-- ===== Cliente ===== --}}
<div class="card p-4 mt-3">
    <div class="qx-hero">
        <div class="qx-av">{{ $initial }}</div>
        <div>
            <h2 class="qx-name">{{ $title }}</h2>
            @if($biz)<div class="small text-secondary mt-1"><i class="bi bi-briefcase"></i> Empresa · contacto {{ $quote->customer_name }}@if($quote->company()['role']) ({{ $quote->company()['role'] }})@endif @if($quote->company()['nit'])· NIT {{ $quote->company()['nit'] }}@endif</div>@endif
            <div class="qx-sub">
                <span class="badge bg-{{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span>
                <span><i class="bi bi-hash"></i>{{ $quote->code() }}</span>
                @if($quote->school && ! $biz)<span><i class="bi bi-building"></i> {{ $quote->school }}</span>@endif
                <span><i class="bi bi-calendar3"></i> {{ $quote->created_at->format('d/m/Y') }}</span>
                <span><i class="bi bi-globe2"></i> {{ match($quote->source) { 'manual' => 'Creada en el panel', 'lead' => 'Leads / Contactos', 'empresa' => 'Cotización empresa', 'personalizada' => 'Personalizada colegio', default => 'Web · diseñador 3D' } }}</span>
                @if($quote->lead_id)<a href="{{ route('admin.leads.show', $quote->lead_id) }}" class="text-secondary"><i class="bi bi-person-lines-fill"></i> Ver contacto</a>@endif
            </div>
        </div>
        <div class="qx-act">
            @if($waChat)<a class="qx-btn wa" href="{{ $waChat }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Chat</a>@endif
            @if($tel)<a class="qx-btn" href="tel:{{ $tel }}"><i class="bi bi-telephone"></i> {{ $quote->customer_phone }}</a>@endif
            @if($quote->customer_email)<a class="qx-btn" href="mailto:{{ $quote->customer_email }}"><i class="bi bi-envelope"></i> Correo</a>@endif
            <a class="qx-btn" href="{{ $quote->publicUrl() }}" target="_blank"><i class="bi bi-eye"></i> Ver como cliente</a>
            <a class="qx-btn" href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
            @if($editUrl)<a class="qx-btn" href="{{ $editUrl }}"><i class="bi bi-pencil"></i> Editar</a>@endif
        </div>
    </div>
    <div class="qx-kpis">
        @if($custom)
            <div class="qx-kpi"><small>{{ $biz ? 'Unidades' : 'Prendas' }}</small><b>{{ $bizTotals['units'] }}</b></div>
        @else
        <div class="qx-kpi"><small>Estudiantes</small><b>{{ $quote->quantity }}</b></div>
        <div class="qx-kpi"><small>Por estudiante</small><b>{{ $unit ? $money($unit) : '—' }}</b></div>
        @endif
        @php $gifts = $quote->giftJackets(); @endphp
        <div class="qx-kpi" title="1 chaqueta de obsequio por cada 25 unidades{{ $quote->giftIsManual() ? ' · cantidad fijada a mano' : '' }}">
            <small><i class="bi bi-gift"></i> Obsequio</small>
            <b class="{{ $gifts ? 'hot' : '' }}">{{ $gifts }} chaqueta{{ $gifts == 1 ? '' : 's' }}</b>
        </div>
        <div class="qx-kpi"><small>Total estimado</small><b class="hot">{{ (float) $quote->estimated_price ? $money($quote->estimated_price) : '—' }}</b></div>
        <div class="qx-kpi"><small>La ha visto</small><b>{{ $quote->view_count ? $quote->view_count.' '.($quote->view_count == 1 ? 'vez' : 'veces') : 'Aún no' }}</b></div>
        <div class="qx-kpi"><small>Próxima llamada</small><b class="{{ $quote->needsFollowUp() ? 'hot' : '' }}">{{ $quote->follow_up_at ? ($quote->needsFollowUp() ? '¡Hoy!' : $quote->follow_up_at->format('d/m')) : '—' }}</b></div>
    </div>
</div>

{{-- ===== Paso a paso ===== --}}
<div class="card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-signpost-split" style="color:#FFC72C"></i> Paso a paso del cliente</h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="small text-secondary d-none d-md-inline">Toca una etapa para mover al cliente</span>
            @if($lost)
                <form method="POST" action="{{ route('admin.quotes.stage', $quote) }}">@csrf<input type="hidden" name="stage" value="reabrir"><button class="qx-ghost"><i class="bi bi-arrow-counterclockwise"></i> Reabrir</button></form>
            @elseif(! $quote->school_id)
                <form method="POST" action="{{ route('admin.quotes.stage', $quote) }}" onsubmit="return confirm('¿Marcar esta cotización como perdida?')">@csrf<input type="hidden" name="stage" value="perdida"><button class="qx-ghost"><i class="bi bi-x-circle"></i> Perdida</button></form>
            @endif
        </div>
    </div>

    <div class="qx-steps {{ $lost ? 'qx-lost' : '' }}">
        @foreach($phases as $phase => $phaseLabel)
            @continue(collect($journey)->where('phase', $phase)->isEmpty())
            <div class="qx-phase" style="--n:{{ collect($journey)->where('phase', $phase)->count() }}">
                <small>{{ $phaseLabel }}</small>
                <div class="qx-row">
                    @foreach(collect($journey)->where('phase', $phase) as $st)
                        <form method="POST" action="{{ route('admin.quotes.stage', $quote) }}" class="qx-step {{ $st['done'] ? 'done' : '' }} {{ $st['current'] && ! $lost ? 'current' : '' }}">
                            @csrf<input type="hidden" name="stage" value="{{ $st['key'] }}">
                            <button type="submit" title="Mover a: {{ $st['label'] }}">
                                <div class="qx-dot"><i class="bi {{ $st['done'] ? 'bi-check-lg' : $st['icon'] }}"></i></div>
                                <span>{{ $st['label'] }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Qué hacer ahora --}}
    @php
        $now = ['icon' => 'bi-lightning-charge', 'title' => '', 'text' => '', 'buttons' => []];
        $stageBtn = fn ($k, $txt) => ['stage' => $k, 'text' => $txt];
        $key = $current['key'] ?? 'entregado';
        if ($lost) {
            $now = ['icon' => 'bi-x-octagon', 'title' => 'Cotización perdida', 'text' => 'No avanzó. Si el cliente vuelve, dale "Reabrir" y sigue desde negociación.', 'buttons' => []];
        } else {
            switch ($key) {
                case 'nuevo':
                    $now = ['icon' => 'bi-chat-heart', 'title' => 'Haz el primer contacto', 'text' => 'Salúdalo por WhatsApp o llámalo, y envíale la cotización desde abajo.',
                        'buttons' => [['href' => '#enviar', 'text' => 'Enviar cotización'], $stageBtn('contactado', 'Ya lo contacté')]];
                    break;
                case 'contactado':
                    $now = ['icon' => 'bi-send', 'title' => 'Envíale la cotización', 'text' => 'Descarga el PDF y envíalo por WhatsApp con el mensaje listo, o por correo.',
                        'buttons' => [['href' => '#enviar', 'text' => 'Ir a enviar'], $stageBtn('enviada', 'Ya se la envié')]];
                    break;
                case 'enviada':
                    $now = ['icon' => 'bi-hourglass-split', 'title' => 'Esperando que la abra', 'text' => 'Te avisamos aquí cuando la vea. Si en 2 días no la abre, escríbele para confirmar que le llegó.',
                        'buttons' => [$stageBtn('negociando', 'Ya estamos negociando')]];
                    break;
                case 'vista':
                    $now = ['icon' => 'bi-fire', 'title' => '¡La abrió '.$quote->view_count.' '.($quote->view_count == 1 ? 'vez' : 'veces').'! Llámalo ya', 'text' => $biz ? 'Es el mejor momento: está interesado. Resuelve dudas y confirma cantidades, tallas y logo.' : 'Es el mejor momento: está interesado. Resuelve dudas y propón la fecha de toma de medidas.',
                        'buttons' => array_values(array_filter([$tel ? ['href' => 'tel:'.$tel, 'text' => 'Llamar'] : null, $stageBtn('negociando', 'Pasar a negociación')]))];
                    break;
                case 'negociando':
                    if ($biz) {
                        $now = ['icon' => 'bi-hand-thumbs-up', 'title' => 'Cierra el trato', 'text' => 'Ajusta prendas o valores si te lo piden (botón Editar) y, cuando la empresa apruebe, márcala como ganada.',
                            'buttons' => [['href' => $editUrl, 'text' => 'Editar cotización'], $stageBtn('ganada', 'Marcar como ganada')]];
                        break;
                    }
                    $now = ['icon' => 'bi-hand-thumbs-up', 'title' => 'Cierra el trato', 'text' => 'Cuando te confirme, conviértelo en colegio: se marca como ganada y pasas al contrato.',
                        'buttons' => [['href' => '#convertir', 'text' => 'Convertir en colegio']]];
                    break;
                case 'ganada':
                    $now = ['icon' => 'bi-file-earmark-plus', 'title' => 'Crea el contrato', 'text' => 'El colegio ya está creado. Haz el contrato y envíalo para firma en línea.',
                        'buttons' => [['href' => route('admin.contracts.create', ['school_id' => $quote->school_id]), 'text' => 'Crear contrato']]];
                    break;
                case 'contrato':
                    $now = ['icon' => 'bi-pen', 'title' => 'Falta la firma del contrato', 'text' => 'Envía el contrato al colegio para que lo firme en línea. Al firmarlo, sigue la toma de medidas.',
                        'buttons' => array_values(array_filter([$contract ? ['href' => route('admin.contracts.show', $contract), 'text' => 'Abrir contrato'] : null]))];
                    break;
                case 'entregado':
                    if ($biz) {
                        $now = ['icon' => 'bi-trophy', 'title' => '¡Cotización ganada!', 'text' => 'Coordina con la empresa el anticipo del 50%, el diseño con su logo y la tabla de tallas.', 'buttons' => []];
                        break;
                    }
                    $now = ['icon' => 'bi-stars', 'title' => '¡Promoción entregada!', 'text' => 'Proceso completo. Pide fotos y una reseña al colegio para tu portafolio.', 'buttons' => []];
                    break;
                default: // producción
                    $inStage = $contract && $contract->production_status === $key;
                    $target = $inStage ? $nextKey : $key;
                    $now = ['icon' => Quote::JOURNEY[$key][1], 'title' => $inStage ? 'Etapa actual: '.$label($key) : 'Empieza: '.mb_strtolower($label($key)),
                        'text' => 'Cuando termines, avanza a la siguiente etapa. El colegio recibe aviso por WhatsApp y lo ve en su enlace.',
                        'buttons' => $target ? [$stageBtn($target, ($inStage ? 'Avanzar a ' : 'Iniciar ').mb_strtolower($label($target)))] : []];
            }
        }
    @endphp
    <div class="qx-now">
        <div class="ic"><i class="bi {{ $now['icon'] }}"></i></div>
        <div style="flex:1;min-width:220px">
            <div class="small text-uppercase" style="letter-spacing:.14em;color:#FFC72C;font-size:10.5px">Ahora toca</div>
            <h6>{{ $now['title'] }}</h6>
            <p>{{ $now['text'] }}</p>
        </div>
        <div class="go">
            @foreach($now['buttons'] as $b)
                @if(isset($b['stage']))
                    <form method="POST" action="{{ route('admin.quotes.stage', $quote) }}">@csrf<input type="hidden" name="stage" value="{{ $b['stage'] }}"><button class="qx-cta">{{ $b['text'] }} <i class="bi bi-arrow-right"></i></button></form>
                @else
                    <a class="qx-cta" href="{{ $b['href'] }}">{{ $b['text'] }} <i class="bi bi-arrow-right"></i></a>
                @endif
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    {{-- ===== Columna izquierda ===== --}}
    <div class="col-xl-7">
        <div class="card p-4 qx-card" id="enviar">
            <h6 class="t"><i class="bi bi-whatsapp" style="color:#25d366"></i> Enviar por WhatsApp</h6>
            <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
                <span class="qx-num">1</span>
                <a href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                <span class="small text-secondary">En la ventana elige “Guardar como PDF”.</span>
            </div>
            <form method="POST" action="{{ route('admin.quotes.whatsapp', $quote) }}" target="_blank" onsubmit="setTimeout(()=>location.reload(),2500)">
                @csrf
                <div class="d-flex gap-2 align-items-center mb-2">
                    <span class="qx-num">2</span>
                    <label class="small text-secondary mb-0">Revisa el mensaje</label>
                </div>
                <textarea name="message" rows="5" class="form-control form-control-sm mb-2" maxlength="2000">{{ $waMessage }}</textarea>
                <label class="d-flex gap-2 small text-secondary mb-3 align-items-center">
                    <input type="checkbox" class="form-check-input mt-0" name="save_default" value="1">
                    Usar este mensaje para todas las cotizaciones
                    <span title="Variables: {nombre} {numero} {enlace} {colegio} {cantidad}"><i class="bi bi-info-circle"></i></span>
                </label>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="qx-num">3</span>
                    <button class="btn" style="background:#25d366;color:#04240f;font-weight:700"><i class="bi bi-send"></i> Abrir WhatsApp y marcar como enviada</button>
                    <span class="small text-secondary">Adjunta el PDF con el clip <i class="bi bi-paperclip"></i> → Documento.</span>
                </div>
            </form>

            <hr class="border-secondary my-4">

            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <h6 class="t mb-2"><i class="bi bi-envelope-paper" style="color:#FFC72C"></i> Por correo</h6>
                    @if($quote->customer_email)
                        <form method="POST" action="{{ route('admin.quotes.email', $quote) }}">@csrf
                            <button class="btn btn-warning w-100" style="font-weight:700"><i class="bi bi-envelope"></i> {{ $quote->activities->where('type', 'correo')->count() ? 'Reenviar' : 'Enviar' }} a {{ $quote->customer_email }}</button>
                        </form>
                    @else
                        <p class="small text-secondary mb-0">Sin correo. Agrégalo en el contacto para enviarla por email.</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="small text-secondary mb-1">Enlace para el cliente</div>
                    <div class="input-group input-group-sm">
                        <input id="pubUrl" class="form-control" value="{{ $quote->publicUrl() }}" readonly onclick="this.select()">
                        <button type="button" class="btn btn-outline-light" onclick="navigator.clipboard.writeText(document.getElementById('pubUrl').value);this.innerHTML='<i class=\'bi bi-check\'></i>'"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>
            </div>
            <div class="small text-secondary d-flex gap-3 flex-wrap mt-3">
                <span><i class="bi bi-send"></i> Enviada: {{ $quote->sent_at ? $quote->sent_at->format('d/m/Y H:i') : 'aún no' }}</span>
                <span><i class="bi bi-eye"></i> Vista: {{ $quote->viewed_at ? $quote->viewed_at->format('d/m/Y H:i') : 'todavía no la abre' }}</span>
            </div>
        </div>

        <div class="card p-4 mt-4 qx-card">
            <h6 class="t"><i class="bi bi-bag-check" style="color:#FFC72C"></i> Lo que se cotizó</h6>
            @if($custom)
                @foreach($quote->businessLines() as $l)
                    <div class="d-flex gap-3 align-items-center py-2" style="border-bottom:1px solid var(--g-line)">
                        @if($l['photo_url'])<img src="{{ $l['photo_url'] }}" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:12px;background:#fff">@else<span style="width:64px;height:64px;border-radius:12px;background:var(--g-surface-2);display:grid;place-items:center"><i class="bi bi-image text-secondary"></i></span>@endif
                        <div class="flex-grow-1 small">
                            <div class="fw-bold" style="font-size:14px">{{ $l['name'] }}</div>
                            <div class="text-secondary">{{ $l['garment'] }}@if($l['features']) · {{ implode(' · ', array_slice($l['features'], 0, 3)) }}@endif</div>
                        </div>
                        <div class="text-end small text-nowrap">{{ $l['qty'] }} × {{ $money($l['unit_price']) }}<br><b style="font-size:15px">{{ $money($l['subtotal']) }}</b></div>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between pt-3" style="color:#FFC72C;font-weight:800;font-size:16px"><span>Total · {{ $bizTotals['units'] }} unidades</span><span>{{ $money($bizTotals['total']) }}</span></div>
                <div class="small text-secondary mt-2"><i class="bi bi-gift"></i> Obsequios: {{ $biz ? 'tula' : 'lo incluido en la prom' }} + {{ $bizTotals['gift_jackets'] ?: 'ninguna' }} chaqueta(s) adicional(es){{ $quote->giftIsManual() ? ' (cantidad fijada a mano)' : ' (1 por cada 25 unidades)' }}</div>
                <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-light mt-3"><i class="bi bi-pencil"></i> Editar prendas, fotos y valores</a>
            @elseif(count($summary['lines']))
                <table class="table table-sm mb-0 qx-sel">
                    @foreach($summary['lines'] as $l)
                        <tr><td>{{ $l['label'] }}</td><td class="text-end">{{ $money($l['price']) }}</td></tr>
                    @endforeach
                    <tr class="tot"><td>Por estudiante</td><td class="text-end">{{ $money($summary['unit_total']) }}</td></tr>
                    @if($quote->quantity > 1)<tr><td class="text-secondary">× {{ $quote->quantity }} estudiantes</td><td class="text-end fw-bold">{{ $money($summary['unit_total'] * $quote->quantity) }}</td></tr>@endif
                </table>
            @else
                <p class="small text-secondary mb-0">Cotización general: el cliente ve todos los precios y combos. Si ya sabes qué quiere, elígelo abajo.</p>
            @endif
            @if($quote->jacketType)<p class="small text-secondary mt-3 mb-0"><i class="bi bi-palette"></i> Diseñó: <b class="text-light">{{ $quote->jacketType->name }}</b> · Parches/logos: {{ $quote->config['patches'] ?? 0 }}</p>@endif
            @if(!empty($quote->config['colors']))
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($quote->config['colors'] as $c)
                        <span class="badge" style="background:{{ $c['hex'] ?? '#333' }};color:#fff;border:1px solid #2a2f3a">{{ $c['label'] ?? '' }} {{ $c['hex'] ?? '' }}</span>
                    @endforeach
                </div>
            @endif

            @unless($custom)
            <details class="mt-3">
                <summary class="small" style="cursor:pointer;color:#FFC72C"><i class="bi bi-pencil"></i> Cambiar prendas o cantidad</summary>
                <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="mt-3">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $quote->status }}">
                    <input type="hidden" name="notes" value="{{ $quote->notes }}">
                    <input type="hidden" name="follow_up_at" value="{{ $quote->follow_up_at?->toDateString() }}">
                    <input type="hidden" name="edit_items" value="1">
                    <label class="form-label small">Estudiantes</label>
                    <input type="number" name="quantity" min="1" value="{{ $quote->quantity }}" class="form-control form-control-sm mb-3">
                    @include('admin.quotes.partials.items', ['selected' => $quote->items])
                    <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar cambios</button>
                </form>
            </details>
            @endunless
        </div>

        @php
            $viewLabels = ['frente' => 'Frente', 'derecha' => 'Lado derecho', 'espalda' => 'Espalda', 'izquierda' => 'Lado izquierdo'];
            $views = $quote->imageUrls();
        @endphp
        @if($views || $quote->imageUrl())
            <div class="card p-4 mt-4 qx-card">
                <h6 class="t"><i class="bi bi-images" style="color:#FFC72C"></i> Diseño del cliente</h6>
                @if($views)
                    <div class="row g-2">
                        @foreach($viewLabels as $k => $lbl)
                            @if(!empty($views[$k]))
                                <div class="col-6 col-md-3">
                                    <a href="{{ $views[$k] }}" target="_blank"><img src="{{ $views[$k] }}" alt="{{ $lbl }}" class="img-fluid rounded border" style="background:#fff"></a>
                                    <div class="small text-secondary text-center mt-1">{{ $lbl }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <img src="{{ $quote->imageUrl() }}" alt="Diseño" class="img-fluid rounded">
                @endif
            </div>
        @endif
    </div>

    {{-- ===== Columna derecha ===== --}}
    <div class="col-xl-5">
        <div class="card p-4 qx-card">
            <h6 class="t"><i class="bi bi-calendar-check" style="color:#FFC72C"></i> Seguimiento</h6>
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="{{ $quote->status }}">
                <label class="form-label small">Próxima llamada</label>
                <input type="hidden" name="gift_mode" value="1">
                <input type="date" name="follow_up_at" id="fu" value="{{ $quote->follow_up_at?->toDateString() }}" class="form-control mb-2">
                <div class="qx-chips">
                    @foreach(['Hoy' => 0, 'Mañana' => 1, 'En 3 días' => 3, 'En 1 semana' => 7] as $t => $d)
                        <button type="button" onclick="document.getElementById('fu').value='{{ today()->addDays($d)->toDateString() }}'">{{ $t }}</button>
                    @endforeach
                    <button type="button" onclick="document.getElementById('fu').value=''">Sin fecha</button>
                </div>
                <label class="form-label small"><i class="bi bi-gift" style="color:#FFC72C"></i> Chaquetas de obsequio</label>
                <div class="input-group mb-1">
                    <input type="number" name="gift_jackets" min="0" max="1000" class="form-control" value="{{ $quote->giftIsManual() ? $quote->giftJackets() : '' }}" placeholder="Automático: {{ $quote->giftJacketsAuto() }}">
                    <span class="input-group-text" style="background:var(--g-surface-3);color:var(--g-bone);border-color:var(--g-line)">prendas gratis</span>
                </div>
                <div class="small text-secondary mb-3">{{ $quote->giftUnits() }} unidades → {{ $quote->giftJacketsAuto() }} automáticas (1 por cada 25). Déjalo vacío para calcularlo solo o escribe otra cantidad si negociaste más.</div>
                <label class="form-label small">Notas internas</label>
                <textarea name="notes" class="form-control mb-3" rows="3" placeholder="Qué busca, presupuesto, quién decide…">{{ $quote->notes }}</textarea>
                <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar</button>
            </form>
        </div>

        @unless($biz)
        <div class="card p-4 mt-4 qx-card" id="convertir" style="border-color:rgba(37,211,102,.45)">
            @if($quote->school_id)
                <h6 class="t"><i class="bi bi-trophy" style="color:#25d366"></i> ¡Cliente ganado!</h6>
                <p class="small text-secondary mb-3">Ya es el colegio <b class="text-light">{{ $quote->schoolRecord?->name }}</b>@if($contract) · contrato {{ $contract->code ?? '#'.$contract->id }} ({{ $contract->stageLabel() }})@endif.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.schools.edit', $quote->school_id) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-building"></i> Ver colegio</a>
                    @if($contract)
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-text"></i> Abrir contrato</a>
                    @else
                        <a href="{{ route('admin.contracts.create', ['school_id' => $quote->school_id]) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-plus"></i> Crear contrato</a>
                    @endif
                </div>
            @else
                <h6 class="t"><i class="bi bi-building-add" style="color:#25d366"></i> ¿Te contrató? Conviértelo en colegio</h6>
                <p class="small text-secondary mb-3">Se crea el colegio con sus datos, la cotización queda <b>Ganada</b> y pasas a crear el contrato.</p>
                <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" onsubmit="return confirm('¿Crear el colegio y marcar la cotización como ganada?')">@csrf
                    <div class="row g-2 mb-3">
                        <div class="col-7"><input name="school_name" value="{{ $quote->school }}" class="form-control form-control-sm" required maxlength="160" placeholder="Nombre del colegio"></div>
                        <div class="col-5"><input name="city" class="form-control form-control-sm" maxlength="120" placeholder="Ciudad"></div>
                    </div>
                    <button class="btn btn-success w-100" style="font-weight:700"><i class="bi bi-check2-circle"></i> Convertir en colegio</button>
                </form>
            @endif
        </div>
        @endunless

        <div class="card p-4 mt-4 qx-card">
            <h6 class="t"><i class="bi bi-clock-history" style="color:#FFC72C"></i> Historial de seguimiento</h6>
            <form method="POST" action="{{ route('admin.quotes.note', $quote) }}" class="d-flex gap-2 mb-3">@csrf
                <input name="note" class="form-control form-control-sm" maxlength="255" placeholder="Ej.: Llamé, piden 45 estudiantes" required>
                <button class="btn btn-sm btn-outline-light text-nowrap"><i class="bi bi-plus"></i> Nota</button>
            </form>
            <div class="qx-tl">
                @forelse($quote->activities as $a)
                    <div class="it">
                        <span class="b" style="background:{{ $a->color() }}22;color:{{ $a->color() }}"><i class="bi {{ $a->icon() }}"></i></span>
                        <div class="small">
                            <div>{{ $a->description }}</div>
                            <div class="text-secondary">{{ $a->created_at->locale('es')->diffForHumans() }} · {{ $a->created_at->format('d/m/Y H:i') }}@if($a->user) · {{ $a->user->name }}@endif</div>
                        </div>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">Aún no hay movimientos.</p>
                @endforelse
            </div>
        </div>

        @if($others->count())
            <div class="card p-4 mt-4 qx-card">
                <h6 class="t"><i class="bi bi-person-lines-fill" style="color:#FFC72C"></i> Otras cotizaciones de este cliente</h6>
                @foreach($others as $o)
                    <a href="{{ route('admin.quotes.show', $o) }}" class="d-flex justify-content-between text-decoration-none small py-2" style="border-bottom:1px solid var(--g-line)">
                        <span>{{ $o->code() }} · {{ $o->created_at->format('d/m/Y') }}</span>
                        <span class="badge bg-{{ $o->statusColor() }}">{{ $o->statusLabel() }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

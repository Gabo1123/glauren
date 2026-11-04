@extends('portal.layout')
@section('title', $contract->code)

@php
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $total = $contract->totalValue();
    $paid  = $contract->paidValue();
    $saldo = max(0, $total - $paid);
    $schoolPaysAll = $contract->school?->schoolPays();
    $deliver = $contract->deliveryDate();
    $stIdx = $contract->stageIndex();
    $stTotal = count($stages);
    $stPct = (int) round(($stIdx + 1) / $stTotal * 100);
    $pendingChanges = $contract->priceChanges->where('status', 'pendiente')->count();
    $needSign = $contract->contract_status === 'enviado' && $contract->sign_token;
    $photosCount = $contract->photos->count();
@endphp

@section('content')
<a href="{{ route('portal.dashboard') }}" class="pc-back"><i class="bi bi-arrow-left"></i> Mis contratos</a>

{{-- Encabezado del contrato --}}
<div class="pc-hero">
    <div class="pc-hero-main">
        <div class="pc-kick">CONTRATO {{ $contract->code }}</div>
        <h1 class="p-h" style="margin:2px 0 4px">{{ $school->name }}</h1>
        <div class="pc-sub">{{ $contract->jacketType?->name ?? 'Chaqueta' }} · Promoción {{ $contract->year }}</div>
        <div class="pc-chips">
            <span class="pc-chip gold"><i class="bi bi-gear"></i> {{ $contract->stageLabel() }}</span>
            @if($contract->isSigned())
                <span class="pc-chip ok"><i class="bi bi-patch-check-fill"></i> Contrato firmado</span>
            @elseif($needSign)
                <span class="pc-chip warn"><i class="bi bi-pen"></i> Por firmar</span>
            @endif
            @if($contract->design_approved_at)<span class="pc-chip ok"><i class="bi bi-palette"></i> Diseño aprobado</span>@endif
        </div>
        <div class="pc-prog">
            <div class="pc-prog-top"><span>Avance del pedido</span><b>{{ $stPct }}%</b></div>
            <div class="pc-bar"><i style="width:{{ $stPct }}%"></i></div>
            <div class="pc-prog-foot">
                @if($contract->production_status === 'entregado')
                    <span style="color:var(--g-ok)"><i class="bi bi-check-circle-fill"></i> ¡Entregado!</span>
                @elseif($deliver)
                    <span><i class="bi bi-truck"></i> Entrega estimada: <b>{{ $deliver->locale('es')->isoFormat('D [de] MMMM YYYY') }}</b></span>
                @else
                    <span><i class="bi bi-truck"></i> La fecha de entrega se calcula al firmar y completar los pagos</span>
                @endif
            </div>
        </div>
    </div>
    <div class="pc-hero-act">
        @if($needSign)
            <a href="{{ route('contrato.firmar', $contract->sign_token) }}" class="pc-btn gold"><i class="bi bi-pen"></i> Firmar contrato</a>
        @endif
        <a href="#mensajes" data-go="mensajes" class="pc-btn"><i class="bi bi-chat-dots"></i> Escribir a G'Lauren</a>
        <a href="#alumnos" data-go="alumnos" class="pc-btn"><i class="bi bi-qr-code"></i> Registrar alumnos</a>
        <a href="{{ route('portal.contract.doc', $contract) }}" target="_blank" class="pc-btn"><i class="bi bi-file-earmark-text"></i> Ver contrato</a>
    </div>
</div>

{{-- Cifras clave --}}
<div class="pc-kpis">
    <div class="pc-kpi"><i class="bi bi-people-fill"></i><b>{{ $pay['total'] }}</b><span>Estudiantes</span></div>
    <div class="pc-kpi ok"><i class="bi bi-check-circle-fill"></i><b>{{ $pay['pagados'] }}</b><span>Pagaron</span></div>
    <div class="pc-kpi bad"><i class="bi bi-clock-history"></i><b>{{ $pay['pendientes'] }}</b><span>Falta pagar</span></div>
    <div class="pc-kpi"><i class="bi bi-cash-coin"></i><b>{{ $saldo > 0 ? $money($saldo) : 'Al día' }}</b><span>Saldo pendiente</span></div>
</div>

{{-- Avisos importantes --}}
@if($pendingChanges)
    <a href="#mensajes" data-go="mensajes" class="pc-alert warn"><i class="bi bi-exclamation-circle"></i> Tienes <b>{{ $pendingChanges }}</b> cambio(s) de precio por aprobar. <u>Revisar</u></a>
@endif

{{-- Pestañas --}}
<nav class="pc-tabs" id="pcTabs">
    <a href="#resumen" data-tab="resumen"><i class="bi bi-house"></i> Resumen</a>
    <a href="#alumnos" data-tab="alumnos"><i class="bi bi-people"></i> Alumnos <em>{{ $pay['total'] }}</em></a>
    <a href="#pagos" data-tab="pagos"><i class="bi bi-wallet2"></i> Pagos @if($pay['pendientes'])<em class="bad">{{ $pay['pendientes'] }}</em>@endif</a>
    <a href="#diseno" data-tab="diseno"><i class="bi bi-palette"></i> Diseño y fotos @if($photosCount)<em>{{ $photosCount }}</em>@endif</a>
    <a href="#mensajes" data-tab="mensajes"><i class="bi bi-chat-dots"></i> Mensajes @if($pendingChanges)<em class="bad">!</em>@endif</a>
    <a href="#documentos" data-tab="documentos"><i class="bi bi-folder2-open"></i> Documentos</a>
</nav>

<div class="pc-pane" data-pane="resumen">
@php $gIdx = $contract->stageIndex(); @endphp
@include('partials.guia', ['guide' => 'portal_colegio', 'at' => $contract->production_status === 'entregado' ? 99 : $gIdx, 'compact' => true])

{{-- Firma pendiente --}}
@if($contract->contract_status === 'enviado' && $contract->sign_token)
    <div style="background:rgba(224,164,34,.1);border:1px solid rgba(224,164,34,.35);border-radius:var(--g-r);padding:20px 22px;margin-bottom:26px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
        <div>
            <div style="font-weight:700;color:var(--g-warn);margin-bottom:3px"><i class="bi bi-pen"></i> Tu contrato está listo para firmar</div>
            <div style="color:var(--g-muted);font-size:14px">Revísalo y fírmalo en línea para iniciar la producción.</div>
        </div>
        <a href="{{ route('contrato.firmar', $contract->sign_token) }}" style="background:var(--g-gold);color:#1a1405;text-decoration:none;font-weight:700;padding:11px 20px;border-radius:var(--g-r-sm);white-space:nowrap">Ver y firmar contrato</a>
    </div>
@elseif($contract->contract_status === 'firmado')
    <div style="background:rgba(37,211,102,.1);border:1px solid rgba(37,211,102,.3);border-radius:var(--g-r);padding:16px 20px;margin-bottom:26px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap">
        <div style="color:var(--g-ok);font-size:14px">
            <i class="bi bi-patch-check-fill"></i> Contrato firmado por <b>{{ $contract->signed_by_name }}</b>@if($contract->signed_at) el {{ $contract->signed_at->format('d/m/Y') }}@endif.
        </div>
        @if($contract->sign_token)
            <a href="{{ route('contrato.firmar', $contract->sign_token) }}" target="_blank" style="border:1px solid var(--g-line);color:var(--g-bone);text-decoration:none;font-size:13px;padding:8px 14px;border-radius:var(--g-r-sm);white-space:nowrap"><i class="bi bi-download"></i> Ver / descargar</a>
        @endif
    </div>
@endif

<div class="pc-grid2">
{{-- Ruta de tu promoción (guía paso a paso) --}}
@php
    $sAlumnos  = $contract->students->count() > 0;
    $sFirmado  = $contract->isSigned();
    $sPagos    = $contract->allStudentsPaid();
    $delivered = $contract->production_status === 'entregado';
    $paidN     = $contract->students->where('payment_status', 'pagado')->count();
    $totalN    = $contract->students->count();
    $eta       = $contract->deliveryDate();

    $steps = [
        ['t' => 'Agrega a tus alumnos',  'd' => $sAlumnos ? ($totalN.' alumno(s) registrado(s)') : 'Registra a toda tu promoción', 'done' => $sAlumnos,  'active' => ! $sAlumnos,             'anchor' => 'alumnos'],
        ['t' => 'Firma del contrato',    'd' => $sFirmado ? 'Contrato firmado ✓' : 'Revísalo y fírmalo para arrancar',            'done' => $sFirmado,  'active' => $sAlumnos && ! $sFirmado, 'anchor' => null],
        ['t' => 'Pago del curso',        'd' => $sPagos ? 'Todos al día ✓' : ($paidN.' de '.$totalN.' han pagado'),                'done' => $sPagos,    'active' => $sFirmado && ! $sPagos,  'anchor' => null],
        ['t' => 'Producción',            'd' => $delivered ? 'Producción terminada' : ($sFirmado ? 'Fabricando tus chaquetas' : 'Inicia al firmar'), 'done' => $delivered, 'active' => $sFirmado && ! $delivered, 'anchor' => null],
        ['t' => 'Entrega',               'd' => $delivered ? '¡Entregado! 🎉' : (($eta && $sPagos) ? 'Estimada: '.$eta->locale('es')->isoFormat('D [de] MMM YYYY') : 'Cuando el curso complete el pago'), 'done' => $delivered, 'active' => false, 'anchor' => null],
    ];
    $doneN = collect($steps)->where('done', true)->count();
    $pct   = (int) round($doneN / count($steps) * 100);
@endphp
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px">
        <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0">🗺️ Ruta de tu promoción</h2>
        <span style="color:var(--g-muted);font-size:13px"><b style="color:var(--g-gold)">{{ $doneN }}</b> de {{ count($steps) }} pasos</span>
    </div>
    <div style="height:7px;background:var(--g-surface-2);border-radius:99px;overflow:hidden;margin-bottom:22px">
        <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--g-gold),#f2c14e);transition:width .4s"></div>
    </div>
    <div style="display:flex;flex-direction:column">
        @foreach($steps as $i => $s)
            @php $isLast = $loop->last; $c = $s['done'] ? 'var(--g-ok)' : ($s['active'] ? 'var(--g-gold)' : 'var(--g-line)'); @endphp
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="display:flex;flex-direction:column;align-items:center;align-self:stretch">
                    <div style="width:30px;height:30px;flex:none;border-radius:50%;display:grid;place-items:center;font-size:14px;font-weight:700;
                        background:{{ $s['done'] || $s['active'] ? $c : 'var(--g-surface-2)' }};
                        color:{{ $s['done'] ? '#04240f' : ($s['active'] ? '#1a1405' : 'var(--g-muted)') }};
                        border:2px solid {{ $c }}">
                        @if($s['done'])<i class="bi bi-check-lg"></i>@else{{ $i + 1 }}@endif
                    </div>
                    @unless($isLast)<div style="width:2px;flex:1;min-height:22px;background:{{ $s['done'] ? 'var(--g-ok)' : 'var(--g-line)' }}"></div>@endunless
                </div>
                <div style="padding-bottom:{{ $isLast ? '0' : '18px' }};flex:1">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span style="font-weight:700;font-size:14.5px;color:{{ $s['active'] ? 'var(--g-gold)' : 'var(--g-bone)' }}">{{ $s['t'] }}</span>
                        @if($s['active'])<span style="background:rgba(224,164,34,.15);color:var(--g-warn);font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:2px 8px;border-radius:99px">Ahora</span>@endif
                    </div>
                    <div style="color:var(--g-muted);font-size:13px;margin-top:2px">{{ $s['d'] }}</div>
                    @if($s['active'] && $s['anchor'])
                        <a href="#{{ $s['anchor'] }}" data-go="{{ $s['anchor'] }}" style="display:inline-block;margin-top:8px;background:var(--g-gold);color:#1a1405;text-decoration:none;font-weight:700;font-size:12.5px;padding:7px 14px;border-radius:8px">Ir a agregar alumnos →</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- Fechas y compromisos --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 16px">Fechas y compromisos</h2>
    @php $rows = [
        ['bi-pen','Firma del contrato', $contract->signed_at ? $contract->signed_at->format('d/m/Y') : 'Pendiente'],
        ['bi-gear','Inicio de producción', $contract->signed_at ? $contract->signed_at->format('d/m/Y') : 'Al firmar'],
        ['bi-truck','Entrega estimada', $deliver ? $deliver->locale('es')->isoFormat('D [de] MMMM YYYY') : 'Por definir'],
        ['bi-cash-coin','Saldo antes de entrega', $saldo > 0 ? $money($saldo) : 'Al día ✓'],
    ]; @endphp
    @foreach($rows as [$ic,$k,$v])
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--g-line)">
            <span style="color:var(--g-muted);font-size:14px"><i class="bi {{ $ic }}"></i> {{ $k }}</span>
            <b style="font-size:14px">{{ $v }}</b>
        </div>
    @endforeach
</section>

</div>
{{-- Línea de tiempo de fabricación --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 22px">Avance de fabricación</h2>
    @php $current = $contract->stageIndex(); $keys = array_keys($stages); @endphp
    <div style="display:flex;flex-wrap:wrap;gap:0">
        @foreach($stages as $key => $label)
            @php $i = $loop->index; $done = $i < $current; $active = $i === $current; @endphp
            <div style="flex:1;min-width:110px;text-align:center;position:relative">
                @if(!$loop->last)
                    <div style="position:absolute;top:13px;left:50%;width:100%;height:2px;background:{{ $i < $current ? 'var(--g-gold)' : 'var(--g-line)' }}"></div>
                @endif
                <div style="position:relative;z-index:2;width:28px;height:28px;border-radius:50%;margin:0 auto 8px;display:grid;place-items:center;font-size:13px;
                    background:{{ $done || $active ? 'var(--g-gold)' : 'var(--g-surface-2)' }};
                    color:{{ $done || $active ? '#1a1405' : 'var(--g-muted)' }};
                    border:2px solid {{ $done || $active ? 'var(--g-gold)' : 'var(--g-line)' }}">
                    @if($done)<i class="bi bi-check"></i>@else{{ $i + 1 }}@endif
                </div>
                <div style="font-size:12px;line-height:1.3;color:{{ $active ? 'var(--g-gold)' : ($done ? 'var(--g-bone)' : 'var(--g-muted)') }};font-weight:{{ $active ? 700 : 400 }}">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>


</div>

<div class="pc-pane" data-pane="alumnos">
{{-- Registro rápido con QR --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 8px">📲 Registro rápido de estudiantes</h2>
    <p style="color:var(--g-muted);font-size:13px;margin:0 0 16px">Muestra este QR en el salón: cada estudiante lo escanea y se registra solo. O comparte el enlace.</p>
    @php $joinUrl = route('join.show', $contract->joinToken()); @endphp
    <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:center">
        <div id="qrbox" style="background:#fff;padding:12px;border-radius:12px;width:170px;height:170px;display:grid;place-items:center"></div>
        <div style="flex:1;min-width:200px">
            <div style="font-family:'Space Mono',monospace;font-size:12px;color:var(--g-muted);word-break:break-all;background:var(--g-surface-2);padding:10px;border-radius:8px">{{ $joinUrl }}</div>
            <button onclick="navigator.clipboard.writeText('{{ $joinUrl }}');this.textContent='✓ Enlace copiado'" style="margin-top:10px;background:var(--g-gold);color:#1a1405;border:0;border-radius:8px;padding:10px 16px;font-weight:600;cursor:pointer;font-size:13px">Copiar enlace</button>
            <a href="https://wa.me/?text={{ urlencode('¡Regístrate en las chaquetas de nuestra promoción! '.$joinUrl) }}" target="_blank" style="display:inline-block;margin-top:10px;margin-left:6px;background:#25d366;color:#04240f;text-decoration:none;font-weight:600;font-size:13px;padding:10px 16px;border-radius:8px">Compartir por WhatsApp</a>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrbox'), { text: @json($joinUrl), width: 146, height: 146, colorDark:'#0d0d0f', colorLight:'#ffffff' });
</script>

{{-- Alumnos --}}
@php $signed = $contract->isSigned(); @endphp
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;scroll-margin-top:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
        <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0">Alumnos <span style="color:var(--g-muted);font-family:var(--g-font-body);font-size:14px">({{ $contract->students->count() }})</span></h2>
        @unless($signed)
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button onclick="var f=document.getElementById('xlsForm');f.style.display=f.style.display==='block'?'none':'block'" style="background:var(--g-gold);border:1px solid var(--g-gold);color:#1a1405;padding:8px 14px;border-radius:var(--g-r-sm);cursor:pointer;font-size:13px;font-weight:700"><i class="bi bi-file-earmark-excel"></i> Subir lista desde Excel</button>
                <button onclick="document.getElementById('addForm').style.display='block';this.style.display='none'" style="background:none;border:1px solid var(--g-gold);color:var(--g-gold);padding:8px 14px;border-radius:var(--g-r-sm);cursor:pointer;font-size:13px"><i class="bi bi-plus-lg"></i> Agregar uno por uno</button>
            </div>
        @endunless
    </div>

    @if($signed)
        {{-- Lista cerrada: el contrato ya está firmado --}}
        <div style="background:rgba(37,211,102,.08);border:1px solid rgba(37,211,102,.3);border-radius:var(--g-r-sm);padding:14px 16px;margin-bottom:18px;font-size:13.5px;color:#cfeede">
            <i class="bi bi-lock-fill" style="color:var(--g-ok)"></i> <b>Lista cerrada.</b> El contrato está firmado con <b>{{ $contract->students->count() }} alumno(s)</b>. Ya no se pueden agregar ni quitar estudiantes. Si necesitas un cambio, escríbenos por <a href="#mensajes" style="color:var(--g-gold)">mensajes</a>.
        </div>
    @else
        {{-- Paso 1: agregar TODOS los alumnos antes de firmar --}}
        <div style="background:rgba(224,164,34,.1);border:1px solid rgba(224,164,34,.35);border-radius:var(--g-r-sm);padding:14px 16px;margin-bottom:18px;font-size:13.5px;color:#f0e3c8">
            <b style="color:var(--g-warn)"><i class="bi bi-1-circle-fill"></i> Paso 1 — Agrega a TODOS los alumnos</b><br>
            Registra a cada estudiante de tu promoción antes de firmar. El total de alumnos define el contrato. <b>Una vez firmado, la lista se cierra.</b>
        </div>
    @endif

    {{-- Cargar todo el curso desde Excel --}}
    @unless($signed)
    <div id="xlsForm" style="display:{{ $errors->has('file') ? 'block' : 'none' }};background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:18px;margin-bottom:18px">
        <div style="font-weight:700;margin-bottom:10px"><i class="bi bi-file-earmark-excel" style="color:var(--g-gold)"></i> Carga a todo el curso en 3 pasos</div>
        <ol style="font-size:13.5px;color:var(--g-muted);margin:0 0 14px;padding-left:18px;line-height:1.7">
            <li><a href="{{ route('portal.students.template', $contract) }}" style="color:var(--g-gold);font-weight:700"><i class="bi bi-download"></i> Descarga el formato</a> (se abre en Excel).</li>
            <li>Llena una fila por alumno: <b>documento</b> y <b>nombre</b> son obligatorios; apodo, celular y sexo son opcionales.</li>
            <li>Guárdalo como <b>CSV</b> y súbelo aquí. Si un alumno ya está, no se repite.</li>
        </ol>
        @error('file')<div style="color:#ff8a80;font-size:13px;margin-bottom:10px"><i class="bi bi-exclamation-triangle"></i> {{ $message }}</div>@enderror
        <form method="POST" action="{{ route('portal.students.import', $contract) }}" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            @csrf
            <input type="file" name="file" accept=".csv,.txt" required style="flex:1;min-width:200px;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:8px;border-radius:6px">
            <button style="background:var(--g-gold);color:#1a1405;border:none;padding:9px 18px;border-radius:6px;font-weight:700;cursor:pointer"><i class="bi bi-upload"></i> Subir lista</button>
        </form>
    </div>
    @endunless

    {{-- Formulario para agregar (colegio solo captura datos básicos) --}}
    @unless($signed)
    <form id="addForm" method="POST" action="{{ route('portal.students.store', $contract) }}" style="display:none;background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:18px;margin-bottom:18px">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:12px">
            <div><label style="font-size:12px;color:var(--g-muted);display:block;margin-bottom:5px">Nombre completo *</label><input name="name" required style="width:100%;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:9px 11px;border-radius:6px;box-sizing:border-box"></div>
            <div><label style="font-size:12px;color:var(--g-muted);display:block;margin-bottom:5px">Documento (T.I./C.C.) *</label><input name="document" required style="width:100%;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:9px 11px;border-radius:6px;box-sizing:border-box"></div>
            <div><label style="font-size:12px;color:var(--g-muted);display:block;margin-bottom:5px">Sexo</label>
                <select name="gender" style="width:100%;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:9px 11px;border-radius:6px;box-sizing:border-box">
                    <option value="">—</option><option value="M">Masculino</option><option value="F">Femenino</option><option value="Otro">Otro</option>
                </select></div>
            <div><label style="font-size:12px;color:var(--g-muted);display:block;margin-bottom:5px">Apodo (para bordar)</label><input name="nickname" style="width:100%;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:9px 11px;border-radius:6px;box-sizing:border-box"></div>
            <div><label style="font-size:12px;color:var(--g-muted);display:block;margin-bottom:5px">Celular / WhatsApp</label><input name="phone" inputmode="tel" placeholder="3001234567" style="width:100%;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:9px 11px;border-radius:6px;box-sizing:border-box"></div>
        </div>
        <div style="font-size:12px;color:var(--g-muted);margin-bottom:12px"><i class="bi bi-info-circle"></i> El equipo Glauren tomará y registrará las medidas de cada alumno.</div>
        <button style="background:var(--g-gold);color:#1a1405;border:none;padding:9px 18px;border-radius:6px;font-weight:700;cursor:pointer">Guardar alumno</button>
    </form>
    @endunless

    @if($contract->students->isEmpty())
        <p style="color:var(--g-muted);font-size:14px;margin:0">Aún no hay alumnos. Agrégalos con el botón de arriba.</p>
    @else
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="text-align:left;color:var(--g-muted);font-family:var(--g-font-label);text-transform:uppercase;font-size:11px;letter-spacing:.06em">
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Nombre</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Documento</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Apodo</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Celular</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Medidas</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)">Pago</th>
                        <th style="padding:9px 8px;border-bottom:1px solid var(--g-line)"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->students as $s)
                        <tr>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line)">{{ $s->name }}</td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line);color:var(--g-muted)">{{ $s->document }}</td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line)">{{ $s->nickname ?: '—' }}</td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line);white-space:nowrap">
                                <details class="ph-edit">
                                    <summary style="cursor:pointer;list-style:none;font-size:13px;color:{{ $s->phone ? 'var(--g-bone)' : 'var(--g-gold)' }}">{!! $s->phone ? e($s->phone).' <i class="bi bi-pencil" style="font-size:11px;color:var(--g-muted)"></i>' : '<i class="bi bi-plus-circle"></i> Agregar' !!}</summary>
                                    <form method="POST" action="{{ route('portal.students.phone', [$contract, $s]) }}" style="display:flex;gap:6px;margin-top:6px">
                                        @csrf @method('PATCH')
                                        <input name="phone" value="{{ $s->phone }}" inputmode="tel" placeholder="3001234567" style="width:130px;background:var(--g-ink);border:1px solid var(--g-line);color:var(--g-bone);padding:6px 8px;border-radius:6px">
                                        <button style="background:var(--g-gold);color:#1a1405;border:0;border-radius:6px;padding:0 10px;font-weight:700;cursor:pointer">OK</button>
                                    </form>
                                </details>
                            </td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line)">
                                @if($s->hasMeasurements())
                                    <span style="color:var(--g-ok);font-size:13px"><i class="bi bi-check-circle"></i> Tomadas</span>
                                @else
                                    <span style="color:var(--g-muted);font-size:13px">Pendiente</span>
                                @endif
                            </td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line)">
                                @if($s->isPaid())
                                    <span style="color:var(--g-ok);font-size:13px"><i class="bi bi-check-circle-fill"></i> Pagó</span>
                                @elseif($s->isUnderReview())
                                    <span style="color:var(--g-warn);font-size:13px"><i class="bi bi-clock"></i> En revisión</span>
                                @else
                                    <span style="color:#ff6b60;font-size:13px"><i class="bi bi-x-circle"></i> Falta</span>
                                @endif
                            </td>
                            <td style="padding:10px 8px;border-bottom:1px solid var(--g-line);text-align:right">
                                @unless($s->hasMeasurements() || $signed)
                                    <form method="POST" action="{{ route('portal.students.destroy', [$contract, $s]) }}" onsubmit="return confirm('¿Eliminar a {{ $s->name }}?')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button style="background:none;border:none;color:var(--g-muted);cursor:pointer" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>


</div>

<div class="pc-pane" data-pane="pagos">
{{-- Resumen de pagos --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 18px">Estado de pagos del curso</h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
        <div style="background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:30px;line-height:1">{{ $pay['total'] }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em">Estudiantes</div>
        </div>
        <div style="background:rgba(37,211,102,.1);border:1px solid rgba(37,211,102,.35);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:30px;line-height:1;color:var(--g-ok)">{{ $pay['pagados'] }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em">Pagaron</div>
        </div>
        <div style="background:rgba(224,164,34,.1);border:1px solid rgba(224,164,34,.35);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:30px;line-height:1;color:var(--g-warn)">{{ $pay['revision'] }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em">En revisión</div>
        </div>
        <div style="background:rgba(255,74,61,.08);border:1px solid rgba(255,74,61,.3);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:30px;line-height:1;color:#ff6b60">{{ $pay['pendientes'] }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em">Falta pagar</div>
        </div>
    </div>
    @php $faltan = $contract->students->filter(fn($s)=>!in_array($s->payment_status,['pagado','en_revision'],true)); @endphp
    @if($faltan->count())
        <div style="font-size:13px;color:var(--g-muted);margin-bottom:6px">Aún no han pagado. Toca un nombre para recordarle por WhatsApp (si tiene celular registrado, le llega directo):</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
            @foreach($faltan as $s)
                @php
                    $digits = preg_replace('/\D/', '', (string) $s->phone);
                    if (strlen($digits) === 10) { $digits = '57'.$digits; }
                    $msg = '¡Hola '.\Illuminate\Support\Str::of($s->name)->explode(' ')->first().'! 👋 Te recordamos el pago de tu chaqueta de la promoción '.$school->name.'. '
                        .'Entra con tu número de documento aquí y paga o sube tu comprobante: '.route('seguimiento').' ¡Gracias! 🧥';
                    $waUrl = 'https://wa.me/'.($digits ?: '').'?text='.rawurlencode($msg);
                @endphp
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" title="Recordarle por WhatsApp{{ $digits ? '' : ' (elige el contacto)' }}" class="remind">
                    <i class="bi bi-whatsapp"></i> {{ $s->nickname ?: $s->name }} <span>Recordar</span>
                </a>
            @endforeach
        </div>
    @else
        <div style="color:var(--g-ok);font-size:14px"><i class="bi bi-check-circle-fill"></i> ¡Todos al día! 🎉</div>
    @endif
</section>

{{-- Resumen económico --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 18px">Valor del contrato</h2>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        <div style="background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:22px">{{ $money($total) }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase">Valor total</div>
        </div>
        <div style="background:rgba(37,211,102,.1);border:1px solid rgba(37,211,102,.35);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:22px;color:var(--g-ok)">{{ $money($paid) }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase">Pagado</div>
        </div>
        <div style="background:rgba(255,74,61,.08);border:1px solid rgba(255,74,61,.3);border-radius:var(--g-r-sm);padding:16px;text-align:center">
            <div style="font-family:var(--g-font-display);font-size:22px;color:#ff6b60">{{ $money($saldo) }}</div>
            <div style="color:var(--g-muted);font-size:11px;text-transform:uppercase">Saldo pendiente</div>
        </div>
    </div>
    @if($schoolPaysAll)<div style="color:var(--g-muted);font-size:12px;margin-top:10px">💼 Modalidad: el colegio gestiona el pago total.</div>@endif
</section>


</div>

<div class="pc-pane" data-pane="diseno">
{{-- Diseño final subido por el equipo --}}
@php $designPhotos = $contract->photos->where('phase', 'diseno'); @endphp
@if($designPhotos->count())
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 8px">🎨 Diseño final</h2>
    <p style="color:var(--g-muted);font-size:13px;margin:0 0 16px">El diseño aprobado de las prendas de la promoción.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">
        @foreach($designPhotos as $dp)
            <a href="{{ $dp->url() }}" target="_blank" style="display:block;border-radius:var(--g-r-sm);overflow:hidden;border:1px solid var(--g-line)">
                <img src="{{ $dp->url() }}" alt="Diseño final" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover;display:block">
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- Diseño aprobado --}}
@if($design)
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 18px">Diseño de tu promoción</h2>
    <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center">
        <a href="{{ $design->imageUrl() }}" target="_blank" style="display:block;width:200px;border-radius:var(--g-r-sm);overflow:hidden;border:1px solid var(--g-line)">
            <img src="{{ $design->imageUrl() }}" alt="Diseño aprobado" style="width:100%;display:block">
        </a>
        <div style="flex:1;min-width:200px;color:var(--g-muted);font-size:14px">
            <div style="color:var(--g-bone);font-weight:600;margin-bottom:4px">{{ $contract->jacketType?->name ?? 'Chaqueta' }}</div>
            Este es el diseño de la promoción. ¿Quieres ajustar algo? Escríbenos abajo por mensajes.
            <div style="margin-top:14px">
                @if($contract->design_approved_at)
                    <span style="color:var(--g-ok);font-size:14px"><i class="bi bi-patch-check-fill"></i> Diseño aprobado el {{ $contract->design_approved_at->format('d/m/Y') }}</span>
                @else
                    <form method="POST" action="{{ route('portal.design.approve', $contract) }}" onsubmit="return confirm('¿Aprobar este diseño como definitivo?')">@csrf
                        <button style="background:var(--g-ok);color:#04240f;border:0;border-radius:8px;padding:11px 20px;font-weight:700;cursor:pointer;font-size:14px"><i class="bi bi-check2-circle"></i> Aprobar diseño final</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- Fotos del proceso --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 18px">Fotos de tu producción</h2>
    @php $processPhotos = $contract->photos->where('phase', '!=', 'diseno'); @endphp
    @if($processPhotos->isEmpty())
        <p style="color:var(--g-muted);font-size:14px;margin:0">Aún no hay fotos. Aparecerán aquí cuando el equipo las cargue.</p>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px">
            @foreach($processPhotos as $photo)
                <a href="{{ $photo->url() }}" target="_blank" style="display:block;aspect-ratio:1;border-radius:var(--g-r-sm);overflow:hidden;border:1px solid var(--g-line)">
                    <img src="{{ $photo->url() }}" alt="Producción" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                </a>
            @endforeach
        </div>
    @endif
</section>


</div>

<div class="pc-pane" data-pane="mensajes">
{{-- Cambios que afectan el precio --}}
@if($contract->priceChanges->count())
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 16px">Cambios por aprobar</h2>
    @foreach($contract->priceChanges as $ch)
        <div style="border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:14px 16px;margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;align-items:start;gap:10px">
                <div><b>{{ $ch->title }}</b>@if($ch->description)<div style="color:var(--g-muted);font-size:13px">{{ $ch->description }}</div>@endif</div>
                <b style="color:{{ $ch->amount_delta >= 0 ? '#ff6b60' : 'var(--g-ok)' }};white-space:nowrap">{{ $ch->amount_delta >= 0 ? '+' : '' }}{{ $money($ch->amount_delta) }}</b>
            </div>
            @if($ch->status === 'pendiente')
                <div style="display:flex;gap:8px;margin-top:10px">
                    <form method="POST" action="{{ route('portal.change.decide', [$contract, $ch]) }}"><input type="hidden" name="decision" value="aprobado">@csrf<button style="background:var(--g-ok);color:#04240f;border:0;border-radius:8px;padding:8px 16px;font-weight:600;cursor:pointer;font-size:13px">✓ Aprobar</button></form>
                    <form method="POST" action="{{ route('portal.change.decide', [$contract, $ch]) }}"><input type="hidden" name="decision" value="rechazado">@csrf<button style="background:none;border:1px solid var(--g-line);color:var(--g-muted);border-radius:8px;padding:8px 16px;cursor:pointer;font-size:13px">Rechazar</button></form>
                </div>
            @else
                <div style="margin-top:6px;font-size:13px;color:{{ $ch->status==='aprobado'?'var(--g-ok)':'var(--g-muted)' }}">{{ $ch->status==='aprobado'?'✓ Aprobado':'Rechazado' }}@if($ch->decided_at) · {{ $ch->decided_at->format('d/m/Y') }}@endif</div>
            @endif
        </div>
    @endforeach
</section>
@endif

{{-- Mensajes con Glauren --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 6px">💬 Mensajes con Glauren</h2>
    <p style="color:var(--g-muted);font-size:13px;margin:0 0 18px">Habla directo con tu asesor sobre el diseño, pagos o la entrega.</p>

    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;max-height:360px;overflow-y:auto">
        @forelse($messages as $m)
            @php $mine = $m->sender === 'colegio'; @endphp
            <div style="align-self:{{ $mine ? 'flex-end' : 'flex-start' }};max-width:78%">
                <div style="background:{{ $mine ? 'var(--g-gold)' : 'var(--g-surface-2)' }};color:{{ $mine ? '#1a1405' : 'var(--g-bone)' }};border:1px solid var(--g-line);border-radius:{{ $mine ? '12px 12px 2px 12px' : '12px 12px 12px 2px' }};padding:10px 14px;font-size:14px">{!! nl2br(e($m->body)) !!}</div>
                <div style="font-size:11px;color:var(--g-muted);margin-top:3px;text-align:{{ $mine ? 'right' : 'left' }}">{{ $mine ? 'Tú' : 'Glauren' }} · {{ $m->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <p style="color:var(--g-muted);font-size:14px;text-align:center;padding:14px 0">Aún no hay mensajes. Escríbenos abajo 👇</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('portal.message', $contract) }}" style="display:flex;gap:10px">
        @csrf
        <input type="text" name="body" maxlength="1000" required placeholder="Escribe tu mensaje…" style="flex:1;background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:var(--g-r-sm);padding:12px 14px;color:var(--g-bone);font-size:14px">
        <button style="background:var(--g-gold);color:#1a1405;border:none;border-radius:var(--g-r-sm);padding:0 20px;font-weight:700;cursor:pointer"><i class="bi bi-send"></i></button>
    </form>
</section>

</div>

<div class="pc-pane" data-pane="documentos">
{{-- Garantías y documentos --}}
<section style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-bottom:22px">
    <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0 0 14px">🛡️ Garantía y condiciones</h2>
    <div style="color:#cfc9bd;font-size:14px;line-height:1.7">
        • <b>Garantía</b> durante el año escolar por defectos de fabricación (confección, cremalleras). No cubre mal uso ni lavado inadecuado.<br>
        • <b>Entrega</b> ~25–35 días hábiles desde la firma, primer pago y aprobación del diseño.<br>
        • Cada prenda incluye etiqueta con instrucciones de cuidado.
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
        <a href="{{ route('portal.contract.doc', $contract) }}" target="_blank" style="background:var(--g-surface-2);border:1px solid var(--g-line);color:var(--g-bone);text-decoration:none;font-size:13px;padding:10px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-file-earmark-text"></i> Ver contrato completo (PDF)</a>
        @if($contract->production_status === 'entregado')
            <a href="{{ route('portal.certificate', $contract) }}" target="_blank" style="background:var(--g-gold);color:#1a1405;text-decoration:none;font-weight:600;font-size:13px;padding:10px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-award"></i> Certificado de entrega</a>
        @endif
    </div>
</section>


</div>

<script>
(function () {
    var tabs = document.querySelectorAll('#pcTabs [data-tab]'), panes = document.querySelectorAll('.pc-pane');
    function show(name, scroll) {
        var ok = false;
        panes.forEach(function (p) { var on = p.dataset.pane === name; p.hidden = !on; ok = ok || on; });
        if (!ok) { return show('resumen'); }
        tabs.forEach(function (t) { t.classList.toggle('on', t.dataset.tab === name); });
        if (history.replaceState) { history.replaceState(null, '', '#' + name); }
        if (scroll) { document.getElementById('pcTabs').scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    }
    tabs.forEach(function (t) { t.addEventListener('click', function (e) { e.preventDefault(); show(t.dataset.tab, false); }); });
    document.querySelectorAll('[data-go]').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.go, true); }); });
    show((location.hash || '#resumen').slice(1), false);
})();
</script>
@endsection

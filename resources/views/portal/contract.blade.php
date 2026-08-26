@extends('portal.layout')
@section('title', $contract->code)

@section('content')
<a href="{{ route('portal.dashboard') }}" style="color:var(--g-muted);text-decoration:none;font-size:14px"><i class="bi bi-arrow-left"></i> Mis contratos</a>

<div style="display:flex;justify-content:space-between;align-items:end;gap:14px;flex-wrap:wrap;margin:14px 0 26px">
    <div>
        <h1 class="p-h" style="margin:0">{{ $contract->code }}</h1>
        <p class="p-sub" style="margin:4px 0 0">{{ $contract->jacketType?->name ?? 'Chaqueta' }} · Promoción {{ $contract->year }}</p>
    </div>
</div>

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
                        <a href="#{{ $s['anchor'] }}" style="display:inline-block;margin-top:8px;background:var(--g-gold);color:#1a1405;text-decoration:none;font-weight:700;font-size:12.5px;padding:7px 14px;border-radius:8px">Ir a agregar alumnos →</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

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
        <div style="font-size:13px;color:var(--g-muted);margin-bottom:6px">Aún no han pagado:</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
            @foreach($faltan as $s)
                <span style="background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:999px;padding:5px 12px;font-size:12.5px">{{ $s->nickname ?: $s->name }}</span>
            @endforeach
        </div>
    @else
        <div style="color:var(--g-ok);font-size:14px"><i class="bi bi-check-circle-fill"></i> ¡Todos al día! 🎉</div>
    @endif
</section>

@php
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $total = $contract->totalValue();
    $paid  = $contract->paidValue();
    $saldo = max(0, $total - $paid);
    $schoolPaysAll = $contract->school?->schoolPays();
    $deliver = $contract->deliveryDate();
@endphp

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
    @if($contract->photos->isEmpty())
        <p style="color:var(--g-muted);font-size:14px;margin:0">Aún no hay fotos. Aparecerán aquí cuando el equipo las cargue.</p>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px">
            @foreach($contract->photos as $photo)
                <a href="{{ $photo->url() }}" target="_blank" style="display:block;aspect-ratio:1;border-radius:var(--g-r-sm);overflow:hidden;border:1px solid var(--g-line)">
                    <img src="{{ $photo->url() }}" alt="Producción" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- Alumnos --}}
@php $signed = $contract->isSigned(); @endphp
<section id="alumnos" style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;scroll-margin-top:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
        <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0">Alumnos <span style="color:var(--g-muted);font-family:var(--g-font-body);font-size:14px">({{ $contract->students->count() }})</span></h2>
        @unless($signed)
            <button onclick="document.getElementById('addForm').style.display='block';this.style.display='none'" style="background:none;border:1px solid var(--g-gold);color:var(--g-gold);padding:8px 14px;border-radius:var(--g-r-sm);cursor:pointer;font-size:13px"><i class="bi bi-plus-lg"></i> Agregar alumno</button>
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

{{-- Mensajes con Glauren --}}
<section id="mensajes" style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:26px;margin-top:22px">
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
@endsection

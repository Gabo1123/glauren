@extends('portal.layout')
@section('title', $school->name)

@section('content')
<h1 class="p-h">Hola, {{ $school->name }}</h1>
<p class="p-sub">Aquí ves tus contratos, el avance de fabricación, las fotos del proceso y tu lista de alumnos.</p>

@php
    $badge = [
        'cotizado'=>'#8a93a2','aprobado'=>'#4f8cff','toma_medidas'=>'#e0a422',
        'en_produccion'=>'#e0a422','control_calidad'=>'#4f8cff','listo'=>'#25d366','entregado'=>'#25d366',
    ];
    $totalStages = count(\App\Modules\Chaquetas\Models\Contract::STAGES);
@endphp

{{-- Avisos --}}
@if(($stats['pending_sign'] ?? 0) > 0)
    <div style="background:rgba(224,164,34,.1);border:1px solid rgba(224,164,34,.35);border-radius:var(--g-r);padding:14px 18px;margin-bottom:16px;color:var(--g-warn);font-size:14px">
        <i class="bi bi-pen"></i> Tienes <b>{{ $stats['pending_sign'] }}</b> contrato(s) por firmar. Ábrelos abajo para firmarlos en línea.
    </div>
@endif
@if(($stats['unread'] ?? 0) > 0)
    <div style="background:rgba(79,140,255,.1);border:1px solid rgba(79,140,255,.35);border-radius:var(--g-r);padding:14px 18px;margin-bottom:16px;color:#7ea6ff;font-size:14px">
        <i class="bi bi-chat-dots"></i> Glauren te envió <b>{{ $stats['unread'] }}</b> mensaje(s) nuevo(s). Míralos en tu contrato.
    </div>
@endif

{{-- Resumen --}}
@if(!$contracts->isEmpty())
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
    @php $sum = [
        ['bi-file-earmark-check', $stats['contracts'], 'Contratos', 'var(--g-gold)'],
        ['bi-people-fill', $stats['students'], 'Estudiantes', 'var(--g-bone)'],
        ['bi-check-circle-fill', $stats['paid'], 'Pagaron', 'var(--g-ok)'],
        ['bi-clock-history', $stats['pending_pay'], 'Falta pagar', '#ff6b60'],
    ]; @endphp
    @foreach($sum as [$ic,$n,$lbl,$col])
        <div style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:18px 16px">
            <i class="bi {{ $ic }}" style="color:{{ $col }};font-size:20px"></i>
            <div style="font-family:var(--g-font-display);font-size:30px;line-height:1;margin-top:6px">{{ $n }}</div>
            <div style="color:var(--g-muted);font-size:12px;text-transform:uppercase;letter-spacing:.05em">{{ $lbl }}</div>
        </div>
    @endforeach
</div>
@endif

@php
    use App\Modules\Chaquetas\Models\Setting;
    $bizWa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573021106592'));
    $signUrl = optional($contracts->firstWhere('contract_status', 'enviado'))->id;
@endphp

@if(!$contracts->isEmpty())
{{-- Ranking + % de pagos + acciones --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
    {{-- % pagos --}}
    <div style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <span style="font-family:var(--g-font-label);text-transform:uppercase;font-size:11px;letter-spacing:.06em;color:var(--g-muted)">Pagos completados</span>
            <span style="font-family:var(--g-font-display);font-size:22px;color:var(--g-ok)">{{ $stats['pay_pct'] }}%</span>
        </div>
        <div style="height:10px;background:var(--g-surface-2);border-radius:999px;overflow:hidden">
            <div style="height:100%;width:{{ $stats['pay_pct'] }}%;background:var(--g-ok)"></div>
        </div>
        <div style="color:var(--g-muted);font-size:12px;margin-top:8px">{{ $stats['paid'] }} de {{ $stats['students'] }} estudiantes al día</div>
    </div>
    {{-- Ranking --}}
    <div style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:20px;display:flex;align-items:center;gap:16px">
        <div style="font-size:34px">🏆</div>
        <div>
            @if($rank)
                <div style="font-family:var(--g-font-display);font-size:26px;line-height:1">#{{ $rank['pos'] }} <span style="color:var(--g-muted);font-size:15px">de {{ $rank['total'] }}</span></div>
                <div style="color:var(--g-muted);font-size:12px">en el ranking de colegios · {{ $rank['votes'] }} votos</div>
                <a href="{{ route('ranking') }}" target="_blank" style="color:var(--g-gold);font-size:12px;text-decoration:none">Ver ranking →</a>
            @else
                <div style="font-family:var(--g-font-display);font-size:18px">Ranking de colegios</div>
                <a href="{{ route('ranking') }}" target="_blank" style="color:var(--g-gold);font-size:12px;text-decoration:none">Ver ranking →</a>
            @endif
        </div>
    </div>
</div>

{{-- Acciones rápidas --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:26px">
    <a href="https://wa.me/?text={{ urlencode('¡Hola promoción! 🎓 Recuerden dejar su pago listo. Consulten su chaqueta y paguen con su documento aquí: '.route('seguimiento')) }}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#25d366;color:#04240f;text-decoration:none;font-weight:600;font-size:14px;padding:11px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-cash-coin"></i> Recordar pago a alumnos</a>
    <a href="https://wa.me/{{ $bizWa }}?text={{ urlencode('Hola Glauren, soy '.$school->name.' y tengo una consulta.') }}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:var(--g-surface);border:1px solid var(--g-line);color:var(--g-bone);text-decoration:none;font-size:14px;padding:11px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-whatsapp"></i> Escribir a Glauren</a>
    @if($signUrl)
        <a href="{{ route('portal.contract', $signUrl) }}" style="display:inline-flex;align-items:center;gap:8px;background:var(--g-gold);color:#1a1405;text-decoration:none;font-weight:600;font-size:14px;padding:11px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-pen"></i> Firmar contrato</a>
    @endif
</div>
@endif

@if($contracts->isEmpty())
    <div style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:40px;text-align:center;color:var(--g-muted)">
        <i class="bi bi-inbox" style="font-size:34px;display:block;margin-bottom:10px;opacity:.6"></i>
        Todavía no tienes contratos registrados. El equipo Glauren los cargará pronto.
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px">
        @foreach($contracts as $c)
            <a href="{{ route('portal.contract', $c) }}" style="text-decoration:none;color:inherit;background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:22px;display:block;transition:var(--g-t)"
               onmouseover="this.style.borderColor='var(--g-gold)'" onmouseout="this.style.borderColor='var(--g-line)'">
                <div style="display:flex;justify-content:space-between;align-items:start;gap:10px;margin-bottom:14px">
                    <div>
                        <div style="font-family:var(--g-font-display);text-transform:uppercase;letter-spacing:.03em;font-size:18px">{{ $c->code }}</div>
                        <div style="color:var(--g-muted);font-size:13px">{{ $c->jacketType?->name ?? 'Chaqueta' }} · {{ $c->year }}</div>
                    </div>
                    <span style="font-size:11px;font-family:var(--g-font-label);letter-spacing:.06em;text-transform:uppercase;color:{{ $badge[$c->production_status] ?? '#8a93a2' }};border:1px solid {{ $badge[$c->production_status] ?? '#8a93a2' }}55;padding:4px 8px;border-radius:20px;white-space:nowrap">{{ $c->stageLabel() }}</span>
                </div>
                @php $pct = (int) round((($c->stageIndex()+1)/$totalStages)*100); @endphp
                <div style="height:7px;background:var(--g-surface-2);border-radius:999px;overflow:hidden;margin-bottom:8px">
                    <div style="height:100%;width:{{ $pct }}%;background:var(--g-gold)"></div>
                </div>
                @php $eta = $c->deliveryDate(); @endphp
                @if($eta && $c->production_status !== 'entregado')
                    <div style="color:var(--g-muted);font-size:12px;margin-bottom:12px"><i class="bi bi-truck"></i> Entrega estimada: <b style="color:var(--g-bone)">{{ $eta->locale('es')->isoFormat('D MMM YYYY') }}</b></div>
                @elseif($c->production_status === 'entregado')
                    <div style="color:var(--g-ok);font-size:12px;margin-bottom:12px"><i class="bi bi-check-circle"></i> Entregado</div>
                @endif
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--g-muted)">
                    <span><i class="bi bi-people"></i> {{ $c->students_count }} alumno{{ $c->students_count == 1 ? '' : 's' }}</span>
                    <span>
                        @if($c->contract_status === 'firmado')
                            <span style="color:var(--g-ok)"><i class="bi bi-patch-check-fill"></i> Firmado</span>
                        @elseif($c->contract_status === 'enviado')
                            <span style="color:var(--g-warn)"><i class="bi bi-pen"></i> Por firmar</span>
                        @endif
                        <i class="bi bi-arrow-right" style="margin-left:8px"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Fotos recientes del proceso --}}
    @if($recentPhotos->count())
    <div style="margin-top:30px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
            <h2 style="font-family:var(--g-font-display);text-transform:uppercase;font-size:16px;letter-spacing:.05em;margin:0"><i class="bi bi-camera"></i> Fotos recientes</h2>
            <span style="color:var(--g-muted);font-size:12px">Últimas del proceso</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px">
            @foreach($recentPhotos as $ph)
                <a href="{{ $ph->url() }}" target="_blank" style="display:block;aspect-ratio:1;border-radius:var(--g-r-sm);overflow:hidden;border:1px solid var(--g-line);position:relative">
                    <img src="{{ $ph->url() }}" alt="Proceso" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                    <span style="position:absolute;left:6px;bottom:6px;background:rgba(0,0,0,.6);color:#fff;font-size:10px;padding:2px 7px;border-radius:10px">{{ $ph->created_at->diffForHumans(null, true) }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif
@endif
@endsection

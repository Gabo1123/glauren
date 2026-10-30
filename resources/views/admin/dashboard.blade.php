@extends('layouts.admin')
@section('title', 'Panel')

@section('content')
@php
    $maxPipe = max(1, $pipeline->max() ?? 1);
    $stageBadge = ['cotizado'=>'#8a8a92','aprobado'=>'#c9922e','toma_medidas'=>'#e0a422','en_produccion'=>'#e0a422','control_calidad'=>'#4f8cff','listo'=>'#25d366','entregado'=>'#25d366'];
@endphp
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Panel del negocio</h3>
        <p class="small text-secondary mb-0">Resumen de {{ now()->translatedFormat('F Y') }}.</p>
    </div>
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-receipt"></i> Registrar gasto</a>
</div>

{{-- Finanzas --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="card p-3 h-100">
        <div class="text-secondary small"><i class="bi bi-cash-coin"></i> Ingresos recibidos</div>
        <div style="font-family:var(--g-font-display);font-size:26px;color:var(--g-ok)">${{ number_format($stats['ingresos_recibidos'],0,',','.') }}</div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100">
        <div class="text-secondary small"><i class="bi bi-hourglass-split"></i> Por cobrar</div>
        <div style="font-family:var(--g-font-display);font-size:26px;color:var(--g-warn)">${{ number_format($stats['por_cobrar'],0,',','.') }}</div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100">
        <div class="text-secondary small"><i class="bi bi-receipt-cutoff"></i> Gastos (histórico)</div>
        <div style="font-family:var(--g-font-display);font-size:26px;color:#e0736b">${{ number_format($stats['gastos'],0,',','.') }}</div>
        <div class="small text-secondary">Este mes: ${{ number_format($stats['gastos_mes'],0,',','.') }}</div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100" style="border-color:{{ $stats['utilidad']>=0?'rgba(37,211,102,.35)':'rgba(224,115,107,.35)' }}">
        <div class="text-secondary small"><i class="bi bi-graph-up-arrow"></i> Utilidad estimada</div>
        <div style="font-family:var(--g-font-display);font-size:26px;color:{{ $stats['utilidad']>=0?'var(--g-ok)':'#e0736b' }}">${{ number_format($stats['utilidad'],0,',','.') }}</div>
        <div class="small text-secondary">Margen {{ $stats['margen'] }}%</div>
    </div></div>
</div>

{{-- Ventas: embudo de cotizaciones --}}
<div class="card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="bi bi-funnel"></i> Ventas · cotizaciones</h6>
        <div class="d-flex gap-2">
            @if($ventas['por_llamar'])<a href="{{ route('admin.quotes.index', ['seguimiento' => 1]) }}" class="btn btn-warning btn-sm"><i class="bi bi-bell"></i> {{ $ventas['por_llamar'] }} por llamar hoy</a>@endif
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva cotización</a>
        </div>
    </div>
    <div class="row g-3 text-center">
        <div class="col-6 col-md-2"><div class="text-secondary small">Este mes</div><div class="fs-4 fw-bold">{{ $ventas['mes'] }}</div></div>
        <div class="col-6 col-md-2"><div class="text-secondary small">Enviadas</div><div class="fs-4 fw-bold">{{ $ventas['enviadas_mes'] }}</div></div>
        <div class="col-6 col-md-2"><div class="text-secondary small">Vistas por cliente</div><div class="fs-4 fw-bold">{{ $ventas['vistas_mes'] }}</div></div>
        <div class="col-6 col-md-2"><div class="text-secondary small">Abiertas</div><div class="fs-4 fw-bold">{{ $ventas['abiertas'] }}</div></div>
        <div class="col-6 col-md-2"><div class="text-secondary small">Ganadas (total)</div><div class="fs-4 fw-bold" style="color:var(--g-ok)">{{ $ventas['ganadas'] }}</div></div>
        <div class="col-6 col-md-2"><div class="text-secondary small">Tasa de cierre</div><div class="fs-4 fw-bold" style="color:var(--g-gold)">{{ $ventas['tasa_cierre'] }}%</div></div>
    </div>
    <div class="small text-secondary mt-2">Valor en cotizaciones abiertas: <b>${{ number_format($ventas['valor_abierto'], 0, ',', '.') }}</b></div>
</div>

{{-- Operación --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><a href="{{ route('admin.contracts.index') }}" class="card p-3 h-100 text-decoration-none d-block"><div class="text-secondary small">Contratos activos</div><div class="fs-3 fw-bold">{{ $stats['contracts_active'] }}<span class="text-secondary fs-6"> / {{ $stats['contracts_total'] }}</span></div></a></div>
    <div class="col-6 col-lg-3"><a href="{{ route('admin.schools.index') }}" class="card p-3 h-100 text-decoration-none d-block"><div class="text-secondary small">Colegios</div><div class="fs-3 fw-bold">{{ $stats['schools'] }}</div></a></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="text-secondary small">Alumnos</div><div class="fs-3 fw-bold">{{ $stats['students'] }}</div></div></div>
    <div class="col-6 col-lg-3"><a href="{{ route('admin.quotes.index') }}" class="card p-3 h-100 text-decoration-none d-block"><div class="text-secondary small">Cotizaciones nuevas</div><div class="fs-3 fw-bold" style="color:var(--g-gold)">{{ $stats['quotes_new'] }}<span class="text-secondary fs-6"> / {{ $stats['quotes'] }}</span></div></a></div>
</div>

<div class="row g-4">
    {{-- Pipeline de producción --}}
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="g-card__title" style="color:var(--g-gold)">Producción en curso</div>
            @foreach($stages as $key => $label)
                @php $n = $pipeline[$key] ?? 0; @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1"><span>{{ $label }}</span><b>{{ $n }}</b></div>
                    <div style="height:9px;background:var(--g-surface-2);border-radius:6px;overflow:hidden">
                        <div style="height:100%;width:{{ round($n/$maxPipe*100) }}%;background:{{ $stageBadge[$key] ?? 'var(--g-gold)' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Entregas próximas --}}
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="g-card__title mb-0" style="color:var(--g-gold)">En fabricación</div>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-outline-light">Ver todos</a>
            </div>
            @forelse($entregas as $c)
                <a href="{{ route('admin.contracts.show', $c) }}" class="d-flex justify-content-between align-items-center py-2 text-decoration-none" style="border-bottom:1px solid var(--g-line);color:inherit">
                    <div>
                        <div class="fw-semibold">{{ $c->code }}</div>
                        <div class="small text-secondary">{{ $c->school?->name }} · {{ $c->students_count }} alumnos</div>
                    </div>
                    <span class="badge" style="background:{{ ($stageBadge[$c->production_status]??'#8a8a92') }}22;color:{{ $stageBadge[$c->production_status]??'#8a8a92' }}">{{ $c->stageLabel() }}</span>
                </a>
            @empty
                <p class="text-secondary small mb-0 mt-2">No hay contratos en fabricación.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    {{-- Cotizaciones recientes --}}
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Cotizaciones recientes</h5>
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-sm btn-outline-light">Ver todas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Cliente</th><th>Tipo</th><th>Cant.</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                    @forelse($recientes as $q)
                        <tr style="cursor:pointer" onclick="location.href='{{ route('admin.quotes.show', $q) }}'">
                            <td>{{ $q->customer_name }}</td>
                            <td>{{ $q->jacketType?->name ?? '—' }}</td>
                            <td>{{ $q->quantity }}</td>
                            <td><span class="badge bg-{{ $q->statusColor() }}">{{ $q->status }}</span></td>
                            <td class="text-secondary small">{{ $q->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Aún no hay cotizaciones.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Gastos recientes --}}
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Gastos recientes</h5>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-outline-light">Ver todos</a>
            </div>
            @forelse($gastosRecientes as $e)
                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--g-line)">
                    <div>
                        <div class="fw-semibold">{{ $e->concept }}</div>
                        <div class="small text-secondary">{{ $e->categoryLabel() }}@if($e->school) · {{ $e->school->name }}@endif · {{ $e->date->format('d/m') }}</div>
                    </div>
                    <b style="color:#e0736b">${{ number_format($e->amount,0,',','.') }}</b>
                </div>
            @empty
                <p class="text-secondary small mb-0 mt-2">Aún no hay gastos.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

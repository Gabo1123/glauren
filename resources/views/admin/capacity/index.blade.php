@extends('layouts.admin')
@section('title', 'Capacidad del taller')

@section('content')
@php $max = max($capacity, collect($plan)->max('total') ?: 1); @endphp
<style>
    .cap-row{display:grid;grid-template-columns:150px 1fr 150px;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid var(--g-line)}
    .cap-row:last-child{border:0}
    .cap-tr{position:relative;height:26px;background:rgba(255,255,255,.06);border-radius:7px;overflow:hidden}
    .cap-tr .c{position:absolute;top:0;bottom:0;left:0;background:#4ea1ff}
    .cap-tr .t{position:absolute;top:0;bottom:0;background:repeating-linear-gradient(45deg,rgba(233,184,63,.55) 0 6px,rgba(233,184,63,.25) 6px 12px)}
    .cap-tr .lim{position:absolute;top:-3px;bottom:-3px;width:3px;background:#fff;border-radius:2px}
    .cap-tr.over .c{background:#ff6b6b}
    .cap-list{font-size:12.5px;color:var(--g-muted,#9a9aa7);margin-top:6px;display:flex;flex-wrap:wrap;gap:6px}
    .cap-list a{color:inherit;border:1px solid var(--g-line);border-radius:20px;padding:2px 9px;text-decoration:none}
    .cap-list a.tent{border-style:dashed}
    .lg{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--g-muted,#9a9aa7);margin-right:14px}
    .lg i{width:12px;height:12px;border-radius:3px;display:inline-block}
    @media(max-width:700px){.cap-row{grid-template-columns:1fr}}
</style>

<div class="g-topbar">
    <div>
        <h3 class="mb-1">Capacidad del taller</h3>
        <p class="small text-secondary mb-0">Cuántas prendas caben por mes y cuántas ya están comprometidas según la fecha de entrega de cada contrato.</p>
    </div>
    <form method="GET"><select name="meses" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach([3, 6, 9, 12] as $m)<option value="{{ $m }}" {{ $months == $m ? 'selected' : '' }}>Próximos {{ $m }} meses</option>@endforeach
    </select></form>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            @if(!$capacity)
                <div class="alert alert-warning small mb-3"><i class="bi bi-exclamation-triangle"></i> Aún no has puesto cuántas prendas saca el taller por mes. Escríbelo a la derecha para recibir alertas cuando un mes se llene.</div>
            @endif
            <div class="mb-2"><span class="lg"><i style="background:#4ea1ff"></i>Contratos aprobados</span><span class="lg"><i style="background:rgba(233,184,63,.55)"></i>En "Cotizado" (tentativo)</span>@if($capacity)<span class="lg"><i style="background:#fff;width:3px"></i>Tope del mes</span>@endif</div>
            @foreach($plan as $row)
                @php $over = $capacity && $row['total'] > $capacity; @endphp
                <div class="cap-row">
                    <div><b>{{ $row['label'] }}</b><div class="small text-secondary">{{ count($row['contracts']) }} contrato(s)</div></div>
                    <div>
                        <div class="cap-tr {{ $over ? 'over' : '' }}">
                            <div class="c" style="width:{{ $row['confirmed'] / $max * 100 }}%"></div>
                            <div class="t" style="left:{{ $row['confirmed'] / $max * 100 }}%;width:{{ $row['tentative'] / $max * 100 }}%"></div>
                            @if($capacity)<div class="lim" style="left:calc({{ $capacity / $max * 100 }}% - 2px)"></div>@endif
                        </div>
                        @if($row['contracts'])
                            <div class="cap-list">
                                @foreach($row['contracts'] as $r)
                                    <a href="{{ route('admin.contracts.show', $r['contract']) }}" class="{{ $r['tentative'] ? 'tent' : '' }}" title="Entrega {{ $r['date']->format('d/m/Y') }}">{{ $r['contract']->school?->name ?? $r['contract']->code }} · {{ $r['units'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div style="font-size:20px;font-weight:800;{{ $over ? 'color:#ff6b6b' : '' }}">{{ $row['total'] }}@if($capacity)<span class="text-secondary" style="font-size:14px;font-weight:400"> / {{ $capacity }}</span>@endif</div>
                        @if($capacity)
                            <div class="small {{ $over ? 'text-danger fw-bold' : ($row['pct'] >= 85 ? 'text-warning' : 'text-success') }}">{{ $over ? '¡Sobrevendido! +'.($row['total'] - $capacity) : ($row['free'].' libres · '.$row['pct'].'%') }}</div>
                        @else
                            <div class="small text-secondary">prendas</div>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="small text-secondary mt-3"><i class="bi bi-info-circle"></i> Se cuentan chaquetas, busos y polos según el combo de cada alumno; las gorras no. Si un contrato aún no tiene alumnos, se estima con su valor total. Los contratos atrasados se suman al mes actual.</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="mb-3"><i class="bi bi-sliders"></i> Ajustes</h6>
            <form method="POST" action="{{ route('admin.capacity.save') }}">
                @csrf @method('PUT')
                <label class="form-label small">Prendas que saca el taller por mes</label>
                <input type="number" name="monthly_capacity" min="0" class="form-control mb-3" value="{{ $capacity ?: '' }}" placeholder="Ej: 600" required>
                <label class="form-label small">Días hábiles de producción (para cotizaciones)</label>
                <input type="number" name="production_days" min="5" max="200" class="form-control mb-1" value="{{ $prodDays }}" required>
                <div class="small text-secondary mb-3">Si firman hoy, se entregaría el <b>{{ $next->locale('es')->isoFormat('D [de] MMMM') }}</b>.</div>
                <label class="form-label small">Anticipo que se pide al firmar</label>
                <div class="input-group mb-1"><input type="number" name="deposit_percent" min="0" max="100" class="form-control" value="{{ $deposit }}" required><span class="input-group-text">%</span></div>
                <div class="small text-secondary mb-3">Se propone solo al crear el contrato desde una cotización. Pon 0 si no pides anticipo.</div>
                <button class="btn btn-primary w-100"><i class="bi bi-check-lg"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection

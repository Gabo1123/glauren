@extends('layouts.admin')
@section('title', 'Cartera')

@section('content')
@php $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.'); @endphp
<style>
    .kp{background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:12px;padding:14px 16px;height:100%}
    .kp .l{font-size:11.5px;text-transform:uppercase;letter-spacing:.06em;color:var(--g-muted,#9a9aa7)}
    .kp .v{font-size:24px;font-weight:800;line-height:1.15;margin-top:4px}
    .pbar{background:rgba(255,255,255,.08);border-radius:6px;height:8px;overflow:hidden;min-width:80px}
    .pbar i{display:block;height:100%;background:#25d366}
</style>
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Cartera</h3>
        <p class="small text-secondary mb-0">Lo recaudado y lo que falta por cobrar en cada colegio. Ordenado por el saldo más alto.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.sales.cartera', $all ? [] : ['todos' => 1]) }}" class="btn btn-outline-light btn-sm">{{ $all ? 'Ocultar contratos en "Cotizado"' : 'Incluir contratos en "Cotizado"' }}</a>
        <a href="{{ route('admin.sales.cartera.export', $all ? ['todos' => 1] : []) }}" class="btn btn-primary btn-sm"><i class="bi bi-file-earmark-excel"></i> Exportar a Excel</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="kp"><div class="l">Valor de contratos</div><div class="v">{{ $money($tot['total']) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="kp"><div class="l">Recaudado</div><div class="v" style="color:#25d366">{{ $money($tot['paid']) }}</div><div class="pbar mt-2"><i style="width:{{ $tot['pct'] }}%"></i></div><div class="small text-secondary mt-1">{{ $tot['pct'] }}%</div></div></div>
    <div class="col-6 col-lg-3"><div class="kp"><div class="l">Por cobrar</div><div class="v" style="color:var(--g-gold,#e9b83f)">{{ $money($tot['balance']) }}</div><div class="small text-secondary">{{ $tot['pending_students'] }} de {{ $tot['students'] }} alumnos deben</div></div></div>
    <div class="col-6 col-lg-3"><div class="kp"><div class="l">Comprobantes por aprobar</div><div class="v">{{ $money($tot['review']) }}</div>@if($tot['review'] > 0)<a href="{{ route('admin.approvals.index') }}" class="small">Revisar →</a>@endif</div></div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Colegio</th><th>Etapa</th><th class="text-center">Alumnos</th><th class="text-end">Valor</th><th class="text-end">Recaudado</th><th class="text-end">Saldo</th><th style="width:150px">Recaudo</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td><a href="{{ route('admin.contracts.show', $r['contract']) }}" style="color:inherit"><b>{{ $r['school'] }}</b></a><div class="small text-secondary">{{ $r['code'] }} · {{ $r['year'] }}</div></td>
                    <td><span class="badge bg-secondary">{{ $r['stage'] }}</span></td>
                    <td class="text-center">{{ $r['students'] }}@if($r['pending_students'])<div class="small text-warning">{{ $r['pending_students'] }} deben</div>@endif</td>
                    <td class="text-end">{{ $money($r['total']) }}</td>
                    <td class="text-end text-success">{{ $money($r['paid']) }}@if($r['review'] > 0)<div class="small text-info">+{{ $money($r['review']) }} en revisión</div>@endif</td>
                    <td class="text-end fw-bold {{ $r['balance'] > 0 ? 'text-warning' : 'text-success' }}">{{ $r['balance'] > 0 ? $money($r['balance']) : 'Al día ✓' }}</td>
                    <td><div class="pbar"><i style="width:{{ $r['pct'] }}%"></i></div><div class="small text-secondary">{{ $r['pct'] }}%</div></td>
                    <td class="text-end text-nowrap">
                        @if($r['pending_students'] > 0)
                            <form method="POST" action="{{ route('admin.reminders.students', $r['contract']) }}" class="d-inline" onsubmit="return confirm('¿Enviar recordatorio de pago por WhatsApp a los {{ $r['pending_students'] }} alumnos que deben?')">@csrf
                                <button class="btn btn-outline-light btn-sm" title="Recordar pago a los que deben"><i class="bi bi-whatsapp"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">Aún no hay contratos con valores.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Finanzas')

@section('content')
@php
    $maxMonth = max(1, collect($months)->max('gastos'));
    $maxUtil = max(1, $porColegio->max('expected') ?: 1);
    $maxCat = max(1, $porCategoria->max() ?: 1);
    $catColors = ['tela'=>'#e9b83f','insumos'=>'#4f8cff','mano_obra'=>'#25d366','transporte'=>'#e0736b','servicios'=>'#b57ce0','otros'=>'#9a9aa7'];
@endphp
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Finanzas</h3>
        <p class="small text-secondary mb-0">Ingresos, gastos y utilidad de todo el negocio.</p>
    </div>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-receipt-cutoff"></i> Ver gastos</a>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="small text-secondary">Ingresos recibidos</div><div style="font-family:var(--g-font-display);font-size:26px;color:var(--g-ok)">${{ number_format($received,0,',','.') }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="small text-secondary">Por cobrar</div><div style="font-family:var(--g-font-display);font-size:26px;color:var(--g-warn)">${{ number_format($pending,0,',','.') }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><div class="small text-secondary">Gastos</div><div style="font-family:var(--g-font-display);font-size:26px;color:#e0736b">${{ number_format($gastos,0,',','.') }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card p-3 h-100" style="border-color:{{ $utilidad>=0?'rgba(37,211,102,.35)':'rgba(224,115,107,.35)' }}"><div class="small text-secondary">Utilidad estimada</div><div style="font-family:var(--g-font-display);font-size:26px;color:{{ $utilidad>=0?'var(--g-ok)':'#e0736b' }}">${{ number_format($utilidad,0,',','.') }}</div><div class="small text-secondary">Margen {{ $margen }}%</div></div></div>
</div>

<div class="row g-4">
    {{-- Gastos por mes --}}
    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <div class="g-card__title" style="color:var(--g-gold)">Gastos por mes</div>
            <div style="display:flex;align-items:flex-end;gap:14px;height:200px;padding-top:10px">
                @foreach($months as $m)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%">
                        <div class="small text-secondary mb-1">${{ number_format($m['gastos']/1000,0) }}k</div>
                        <div title="${{ number_format($m['gastos'],0,',','.') }}" style="width:70%;background:linear-gradient(180deg,#e0736b,#b5433c);border-radius:6px 6px 0 0;height:{{ round(($m['gastos']/$maxMonth)*100) }}%;min-height:3px"></div>
                        <div class="small mt-2">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Gastos por categoría --}}
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <div class="g-card__title" style="color:var(--g-gold)">Gastos por categoría</div>
            @forelse($porCategoria as $cat => $t)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1"><span>{{ $categories[$cat] ?? $cat }}</span><b>${{ number_format($t,0,',','.') }}</b></div>
                    <div style="height:10px;background:var(--g-surface-2);border-radius:6px;overflow:hidden"><div style="height:100%;width:{{ round(($t/$maxCat)*100) }}%;background:{{ $catColors[$cat] ?? 'var(--g-gold)' }}"></div></div>
                </div>
            @empty
                <p class="text-secondary small mb-0">Aún no hay gastos registrados.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Utilidad por colegio --}}
<div class="card p-4 mt-4">
    <div class="g-card__title" style="color:var(--g-gold)">Utilidad por colegio</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Colegio</th><th class="text-center">Alumnos</th><th class="text-end">Ingresos</th><th class="text-end">Recibido</th><th class="text-end">Gastos</th><th class="text-end">Utilidad</th></tr></thead>
            <tbody>
            @forelse($porColegio as $r)
                <tr>
                    <td>{{ $r['name'] }}</td>
                    <td class="text-center">{{ $r['alumnos'] }}</td>
                    <td class="text-end">${{ number_format($r['expected'],0,',','.') }}</td>
                    <td class="text-end" style="color:var(--g-ok)">${{ number_format($r['received'],0,',','.') }}</td>
                    <td class="text-end" style="color:#e0736b">${{ number_format($r['gastos'],0,',','.') }}</td>
                    <td class="text-end fw-bold" style="color:{{ $r['utilidad']>=0?'var(--g-ok)':'#e0736b' }}">${{ number_format($r['utilidad'],0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Aún no hay datos por colegio.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

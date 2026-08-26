@extends('layouts.admin')
@section('title', 'Gastos / Facturas')

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Gastos / Facturas</h3>
        <p class="small text-secondary mb-0">Registra tus compras, sube la factura y archívala por colegio, contrato o tipo de tela.</p>
    </div>
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo gasto</a>
</div>

{{-- Totales --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card p-3">
            <div class="small text-secondary">Total {{ $filters['month'] ?? 'histórico' }}</div>
            <div style="font-family:var(--g-font-display);font-size:30px;color:var(--g-gold)">${{ number_format($total,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 h-100">
            <div class="small text-secondary mb-2">Por categoría</div>
            <div class="d-flex flex-wrap gap-2">
                @forelse($byCat as $cat => $sum)
                    <span class="badge bg-secondary" style="font-size:13px;padding:7px 11px">{{ $categories[$cat] ?? $cat }}: <b class="ms-1" style="color:var(--g-gold)">${{ number_format($sum,0,',','.') }}</b></span>
                @empty
                    <span class="text-secondary small">Sin datos.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">Categoría</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">Todas</option>
                @foreach($categories as $k=>$v)<option value="{{ $k }}" {{ ($filters['category']??'')===$k?'selected':'' }}>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small">Colegio</label>
            <select name="school" class="form-select form-select-sm">
                <option value="">Todos</option>
                @foreach($schools as $s)<option value="{{ $s->id }}" {{ (string)($filters['school']??'')===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small">Mes</label>
            <input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-light btn-sm">Limpiar</a>
        </div>
    </div>
</form>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Concepto</th><th>Categoría</th><th>Asignado a</th><th class="text-end">Valor</th><th class="text-center">Factura</th><th></th></tr></thead>
            <tbody>
            @forelse($expenses as $e)
                <tr>
                    <td class="text-secondary" style="white-space:nowrap">{{ $e->date->format('d/m/Y') }}</td>
                    <td>{{ $e->concept }}@if($e->supplier)<div class="small text-secondary">{{ $e->supplier }}</div>@endif</td>
                    <td><span class="badge bg-secondary">{{ $e->categoryLabel() }}</span></td>
                    <td class="small">
                        @if($e->school)<div><i class="bi bi-building"></i> {{ $e->school->name }}</div>@endif
                        @if($e->jacketType)<div class="text-secondary"><i class="bi bi-tags"></i> {{ $e->jacketType->name }}</div>@endif
                        @if($e->material)<div class="text-secondary"><i class="bi bi-rulers"></i> {{ $e->material }}</div>@endif
                        @if(!$e->school && !$e->jacketType && !$e->material)<span class="text-secondary">—</span>@endif
                    </td>
                    <td class="text-end fw-bold">${{ number_format($e->amount,0,',','.') }}</td>
                    <td class="text-center">
                        @if($e->invoiceUrl())
                            <a href="{{ $e->invoiceUrl() }}" target="_blank" title="Ver factura">
                                @if($e->invoiceIsPdf())<i class="bi bi-file-earmark-pdf" style="font-size:22px;color:#e0736b"></i>
                                @else<img src="{{ $e->invoiceUrl() }}" style="width:46px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--g-line)">@endif
                            </a>
                        @else<span class="text-secondary">—</span>@endif
                    </td>
                    <td class="text-end" style="white-space:nowrap">
                        <a href="{{ route('admin.expenses.edit', $e) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.expenses.destroy', $e) }}" class="d-inline" onsubmit="return confirm('¿Eliminar gasto?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No hay gastos registrados con estos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', $L['title'])

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">{{ $L['title'] }}</h3>
        <p class="small text-secondary mb-0">{{ $L['subtitle'] }}</p>
    </div>
    <a href="{{ route($rp.'.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ $L['new'] }}</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-3"><div class="small text-secondary">En seguimiento</div><div class="fs-3 fw-bold">{{ $stats['abiertas'] }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="small text-secondary">Ganadas</div><div class="fs-3 fw-bold" style="color:var(--g-ok)">{{ $stats['ganadas'] }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div class="small text-secondary">Valor en seguimiento</div><div class="fs-3 fw-bold" style="color:#FFC72C">${{ number_format($stats['valor'], 0, ',', '.') }}</div></div></div>
</div>

<form class="d-flex gap-2 mb-3" method="GET">
    <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar {{ mb_strtolower($L['entity']) }}, contacto, celular o número…">
    <button class="btn btn-outline-light"><i class="bi bi-search"></i></button>
</form>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>N°</th><th>{{ $L['entity'] }}</th><th>Contacto</th><th>Prendas</th><th class="text-end">Valor total</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
            @forelse($quotes as $qt)
                @php $t = $qt->businessTotals(); $lines = $qt->businessLines(); @endphp
                <tr style="cursor:pointer" onclick="location='{{ route('admin.quotes.show', $qt) }}'">
                    <td class="fw-bold">{{ $qt->number }}</td>
                    <td>
                        <div class="fw-bold">{{ $qt->school }}</div>
                        @if($qt->company()['nit'])<div class="small text-secondary">NIT {{ $qt->company()['nit'] }}</div>@endif
                        @if($qt->company()['course'])<div class="small text-secondary">{{ $qt->company()['course'] }}</div>@endif
                    </td>
                    <td class="small">{{ $qt->customer_name }}<br><span class="text-secondary">{{ $qt->customer_phone }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            @foreach(array_slice($lines, 0, 4) as $l)
                                @if($l['photo_url'])<img src="{{ $l['photo_url'] }}" alt="" style="width:34px;height:34px;object-fit:cover;border-radius:8px;border:1px solid var(--g-line)">@endif
                            @endforeach
                            <span class="small text-secondary ms-1">{{ count($lines) }} prenda{{ count($lines) == 1 ? '' : 's' }} · {{ $t['units'] }} {{ $L['school'] ? 'est.' : 'und.' }}</span>
                        </div>
                    </td>
                    <td class="text-end fw-bold">${{ number_format($t['total'], 0, ',', '.') }}</td>
                    <td><span class="badge bg-{{ $qt->statusColor() }}">{{ $qt->statusLabel() }}</span>@if($qt->viewed_at) <i class="bi bi-eye text-info" title="La vio {{ $qt->view_count }} veces"></i>@endif</td>
                    <td class="small text-secondary">{{ $qt->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-5">
                    {{ $L['empty'] }}<br>
                    <a href="{{ route($rp.'.create') }}" class="btn btn-primary btn-sm mt-3"><i class="bi bi-plus-lg"></i> Crear la primera</a>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $quotes->links() }}</div>
@endsection

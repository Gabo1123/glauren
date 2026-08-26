@extends('layouts.admin')
@section('title', 'Cotizaciones')

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Cotizaciones</h3>
        <p class="small text-secondary mb-0">Diseños que enviaron tus clientes. Contáctalos rápido por WhatsApp.</p>
    </div>
    <span class="badge bg-secondary" style="font-size:14px;padding:8px 12px">{{ $quotes->total() }} en total</span>
</div>

<form method="GET" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6"><label class="form-label small">Buscar</label><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nombre, teléfono, correo o colegio"></div>
        <div class="col-md-3"><label class="form-label small">Estado</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Todas</option>
                @foreach(['nuevo','contactado','cerrado','cancelado'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Buscar</button>
            <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-light btn-sm">Limpiar</a>
        </div>
    </div>
</form>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Diseño</th><th>Cliente</th><th>Tipo</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @forelse($quotes as $q)
                <tr style="cursor:pointer" onclick="if(!event.target.closest('a'))location.href='{{ route('admin.quotes.show', $q) }}'">
                    <td>
                        @if($q->imageUrl())
                            <img src="{{ $q->imageUrl() }}" alt="Diseño" style="width:54px;height:54px;object-fit:cover;border-radius:8px;border:1px solid var(--g-line);background:#0e1116">
                        @else
                            <div style="width:54px;height:54px;border-radius:8px;background:var(--g-surface-2);display:grid;place-items:center;color:var(--g-muted)"><i class="bi bi-image"></i></div>
                        @endif
                    </td>
                    <td>{{ $q->customer_name }}<div class="small text-secondary">{{ $q->customer_phone }}@if($q->school) · {{ $q->school }}@endif</div></td>
                    <td>{{ $q->jacketType?->name ?? '—' }}</td>
                    <td class="text-center">{{ $q->quantity }}</td>
                    <td class="text-end fw-semibold">${{ number_format($q->estimated_price, 0, ',', '.') }}</td>
                    <td><span class="badge bg-{{ $q->statusColor() }}">{{ $q->status }}</span></td>
                    <td class="text-secondary small" style="white-space:nowrap">{{ $q->created_at->format('d/m/Y') }}<br>{{ $q->created_at->format('H:i') }}</td>
                    <td class="text-end" style="white-space:nowrap">
                        @if($q->customer_phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/','',$q->customer_phone) }}?text={{ urlencode('Hola '.$q->customer_name.'! Vimos tu diseño de chaqueta de prom en Glauren. ¿Coordinamos tu cotización?') }}" target="_blank" rel="noopener" class="btn btn-sm" style="background:#25d366;color:#04240f" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                        <a href="{{ route('admin.quotes.show', $q) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">No hay cotizaciones con esos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $quotes->links() }}</div>
@endsection

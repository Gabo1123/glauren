@extends('layouts.admin')
@section('title', 'Cotización #'.$quote->id)

@section('content')
<a href="{{ route('admin.quotes.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
<h3 class="my-3">Cotización #{{ $quote->id }} <span class="badge bg-{{ $quote->statusColor() }}">{{ $quote->status }}</span></h3>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-3">Detalle</h5>
            <dl class="row mb-0">
                <dt class="col-4 text-secondary">Cliente</dt><dd class="col-8">{{ $quote->customer_name }}</dd>
                <dt class="col-4 text-secondary">WhatsApp</dt><dd class="col-8"><a href="https://wa.me/{{ preg_replace('/\D/', '', $quote->customer_phone) }}" target="_blank">{{ $quote->customer_phone }}</a></dd>
                @if($quote->customer_email)<dt class="col-4 text-secondary">Correo</dt><dd class="col-8">{{ $quote->customer_email }}</dd>@endif
                @if($quote->school)<dt class="col-4 text-secondary">Colegio</dt><dd class="col-8">{{ $quote->school }}</dd>@endif
                <dt class="col-4 text-secondary">Tipo</dt><dd class="col-8">{{ $quote->jacketType?->name ?? '—' }}</dd>
                <dt class="col-4 text-secondary">Cantidad</dt><dd class="col-8">{{ $quote->quantity }}</dd>
                <dt class="col-4 text-secondary">Precio estimado</dt><dd class="col-8 fw-bold text-success">${{ number_format($quote->estimated_price, 0, ',', '.') }}</dd>
                <dt class="col-4 text-secondary">Fecha</dt><dd class="col-8">{{ $quote->created_at->format('d/m/Y H:i') }}</dd>
            </dl>

            @if(!empty($quote->config['colors']))
                <hr class="border-secondary">
                <h6>Colores</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($quote->config['colors'] as $c)
                        <span class="badge" style="background:{{ $c['hex'] ?? '#333' }}; color:#fff; border:1px solid #2a2f3a">{{ $c['label'] ?? '' }} {{ $c['hex'] ?? '' }}</span>
                    @endforeach
                </div>
            @endif
            <p class="small text-secondary mt-3 mb-0">Parches/logos: {{ $quote->config['patches'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-lg-5">
        @php
            $labels = ['frente' => 'Frente', 'derecha' => 'Lado derecho', 'espalda' => 'Espalda', 'izquierda' => 'Lado izquierdo'];
            $views = $quote->imageUrls();
        @endphp
        @if($views)
            <div class="card p-3 mb-4">
                <h6 class="mb-2"><i class="bi bi-images"></i> Diseño ({{ count($views) }} vista{{ count($views) > 1 ? 's' : '' }})</h6>
                <div class="row g-2">
                    @foreach($labels as $key => $lbl)
                        @if(!empty($views[$key]))
                            <div class="col-6">
                                <a href="{{ $views[$key] }}" target="_blank" class="d-block">
                                    <img src="{{ $views[$key] }}" alt="{{ $lbl }}" class="img-fluid rounded border" style="background:#fff">
                                </a>
                                <div class="small text-secondary text-center mt-1">{{ $lbl }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @elseif($quote->imageUrl())
            <div class="card p-2 mb-4">
                <img src="{{ $quote->imageUrl() }}" alt="Diseño" class="img-fluid rounded">
            </div>
        @endif

        <div class="card p-4">
            <h6 class="mb-3">Gestión</h6>
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}">
                @csrf @method('PUT')
                <label class="form-label small">Estado</label>
                <select name="status" class="form-select mb-3">
                    @foreach(['nuevo','contactado','cerrado','cancelado'] as $s)
                        <option value="{{ $s }}" {{ $quote->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <label class="form-label small">Notas internas</label>
                <textarea name="notes" class="form-control mb-3" rows="4">{{ $quote->notes }}</textarea>
                <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection

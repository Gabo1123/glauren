@extends('layouts.admin')
@section('title', 'Cotización '.$quote->code())

@section('content')
<a href="{{ route('admin.quotes.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
<h3 class="my-3">Cotización {{ $quote->code() }} <span class="badge bg-{{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span></h3>

<div class="card p-3 mb-4" style="border-color:rgba(255,199,44,.45)">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="me-auto">
            <div class="small text-secondary">Enlace para el cliente</div>
            <input id="pubUrl" class="form-control form-control-sm" style="min-width:320px" value="{{ $quote->publicUrl() }}" readonly onclick="this.select()">
        </div>
        <a href="{{ route('admin.quotes.send', $quote) }}" target="_blank" rel="noopener" class="btn btn-sm" style="background:#25d366;color:#04240f"><i class="bi bi-whatsapp"></i> Enviar por WhatsApp</a>
        @if($quote->customer_email)
            <form method="POST" action="{{ route('admin.quotes.email', $quote) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-envelope"></i> {{ $quote->sent_at ? 'Reenviar' : 'Enviar' }} por correo</button>
            </form>
        @endif
        <button type="button" class="btn btn-sm btn-outline-light" onclick="navigator.clipboard.writeText(document.getElementById('pubUrl').value);this.innerText='¡Copiado!'"><i class="bi bi-clipboard"></i> Copiar enlace</button>
        <a href="{{ $quote->publicUrl() }}" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-eye"></i> Ver como cliente</a>
    </div>
    <div class="small text-secondary mt-2 d-flex flex-wrap gap-3">
        <span><i class="bi bi-send"></i> Enviada: {{ $quote->sent_at ? $quote->sent_at->format('d/m/Y H:i') : 'aún no' }}</span>
        <span><i class="bi bi-eye"></i> Vista por el cliente: {{ $quote->viewed_at ? $quote->viewed_at->format('d/m/Y H:i').' ('.$quote->view_count.' '.($quote->view_count == 1 ? 'vez' : 'veces').')' : 'todavía no la abre' }}</span>
        <span><i class="bi bi-calendar-event"></i> Seguimiento: {{ $quote->follow_up_at ? $quote->follow_up_at->format('d/m/Y') : 'sin fecha' }}@if($quote->needsFollowUp()) <b class="text-warning">· ¡hoy toca!</b>@endif</span>
        <span><i class="bi bi-globe"></i> Origen: {{ $quote->source === 'manual' ? 'creada en el panel' : 'web / diseñador 3D' }}</span>
    </div>
</div>

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

            @if(count($summary['lines']))
                <hr class="border-secondary">
                <h6>Selección cotizada</h6>
                <table class="table table-sm mb-0">
                    @foreach($summary['lines'] as $l)
                        <tr><td>{{ $l['label'] }}</td><td class="text-end">${{ number_format($l['price'], 0, ',', '.') }}</td></tr>
                    @endforeach
                    <tr><th>Por estudiante</th><th class="text-end">${{ number_format($summary['unit_total'], 0, ',', '.') }}</th></tr>
                </table>
            @endif

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
                    @foreach(\App\Modules\Chaquetas\Models\Quote::STATUSES as $s => $label)
                        <option value="{{ $s }}" {{ $quote->status === $s ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label small">Próximo seguimiento</label>
                <input type="date" name="follow_up_at" value="{{ $quote->follow_up_at?->toDateString() }}" class="form-control mb-3">
                <label class="form-label small">Notas internas</label>
                <textarea name="notes" class="form-control mb-3" rows="4">{{ $quote->notes }}</textarea>

                <details class="mb-3">
                    <summary class="small text-secondary" style="cursor:pointer">Editar prendas y cantidad de la cotización</summary>
                    <div class="mt-3">
                        <input type="hidden" name="edit_items" value="0" id="editItems">
                        <label class="form-label small">Estudiantes</label>
                        <input type="number" name="quantity" min="1" value="{{ $quote->quantity }}" class="form-control form-control-sm mb-3" onchange="document.getElementById('editItems').value=1">
                        <div onchange="document.getElementById('editItems').value=1">
                            @include('admin.quotes.partials.items', ['selected' => $quote->items])
                        </div>
                    </div>
                </details>
                <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection

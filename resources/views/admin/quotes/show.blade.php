@extends('layouts.admin')
@section('title', 'Cotización '.$quote->code())

@section('content')
<a href="{{ route('admin.quotes.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
<h3 class="my-3">Cotización {{ $quote->code() }} <span class="badge bg-{{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span></h3>

@if($quote->lead_id)
    <p class="small text-secondary mt-n2"><i class="bi bi-person-lines-fill"></i> Viene de <a href="{{ route('admin.leads.show', $quote->lead_id) }}">Leads / Contactos</a>@if($quote->isGeneral()) · se envía como cotización general (sin valores en el correo)@endif</p>
@endif

{{-- ===== Enviar al cliente ===== --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card p-4 h-100" style="border-color:rgba(37,211,102,.45)">
            <h6 class="mb-3"><i class="bi bi-whatsapp" style="color:#25d366"></i> Enviar por WhatsApp</h6>
            <div class="d-flex gap-2 align-items-center mb-3">
                <span class="badge rounded-pill" style="background:#FFC72C;color:#0E0E0E">1</span>
                <a href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF de la cotización</a>
                <span class="small text-secondary">En la ventana que se abre elige “Guardar como PDF”.</span>
            </div>
            <form method="POST" action="{{ route('admin.quotes.whatsapp', $quote) }}" target="_blank" onsubmit="setTimeout(()=>location.reload(),2500)">
                @csrf
                <div class="d-flex gap-2 align-items-center mb-2">
                    <span class="badge rounded-pill" style="background:#FFC72C;color:#0E0E0E">2</span>
                    <label class="small text-secondary mb-0">Mensaje (puedes editarlo antes de enviar)</label>
                </div>
                <textarea name="message" rows="6" class="form-control form-control-sm mb-2" maxlength="2000">{{ $waMessage }}</textarea>
                <label class="d-flex gap-2 small text-secondary mb-3 align-items-center">
                    <input type="checkbox" class="form-check-input mt-0" name="save_default" value="1">
                    Guardar como mensaje predeterminado para todas las cotizaciones
                    <span title="Variables: {nombre} {numero} {enlace} {colegio} {cantidad}"><i class="bi bi-info-circle"></i></span>
                </label>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge rounded-pill" style="background:#FFC72C;color:#0E0E0E">3</span>
                    <button class="btn" style="background:#25d366;color:#04240f;font-weight:700"><i class="bi bi-send"></i> Abrir WhatsApp y marcar como enviada</button>
                </div>
                <p class="small text-secondary mt-2 mb-0">En WhatsApp adjunta el PDF que descargaste (clip <i class="bi bi-paperclip"></i> → Documento) y envía.</p>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 h-100" style="border-color:rgba(255,199,44,.45)">
            <h6 class="mb-3"><i class="bi bi-envelope-paper" style="color:#FFC72C"></i> Enviar por correo</h6>
            @if($quote->customer_email)
                <p class="small text-secondary mb-3">Se envía el correo con el diseño G'Lauren a <b class="text-light">{{ $quote->customer_email }}</b>.</p>
                <form method="POST" action="{{ route('admin.quotes.email', $quote) }}">@csrf
                    <button class="btn btn-warning w-100 mb-2" style="font-weight:700"><i class="bi bi-envelope"></i> {{ $quote->activities->where('type', 'correo')->count() ? 'Reenviar' : 'Enviar' }} por correo</button>
                </form>
            @else
                <p class="small text-secondary mb-3">Este cliente no dejó correo. Agrégalo para poder enviarle la cotización por email.</p>
            @endif
            <hr class="border-secondary">
            <div class="small text-secondary mb-1">Enlace de la cotización</div>
            <div class="input-group input-group-sm mb-2">
                <input id="pubUrl" class="form-control" value="{{ $quote->publicUrl() }}" readonly onclick="this.select()">
                <button type="button" class="btn btn-outline-light" onclick="navigator.clipboard.writeText(document.getElementById('pubUrl').value);this.innerHTML='<i class=\'bi bi-check\'></i>'"><i class="bi bi-clipboard"></i></button>
                <a href="{{ $quote->publicUrl() }}" target="_blank" class="btn btn-outline-light" title="Ver como cliente"><i class="bi bi-eye"></i></a>
            </div>
            <div class="small text-secondary d-flex flex-column gap-1 mt-2">
                <span><i class="bi bi-send"></i> Enviada: {{ $quote->sent_at ? $quote->sent_at->format('d/m/Y H:i') : 'aún no' }}</span>
                <span><i class="bi bi-eye"></i> Vista: {{ $quote->viewed_at ? $quote->viewed_at->format('d/m/Y H:i').' · '.$quote->view_count.' '.($quote->view_count == 1 ? 'vez' : 'veces') : 'todavía no la abre' }}</span>
                <span><i class="bi bi-calendar-event"></i> Seguimiento: {{ $quote->follow_up_at ? $quote->follow_up_at->format('d/m/Y') : 'sin fecha' }}@if($quote->needsFollowUp()) <b class="text-warning">· ¡hoy toca!</b>@endif</span>
                <span><i class="bi bi-globe"></i> Origen: {{ $quote->source === 'manual' ? 'creada en el panel' : 'web / diseñador 3D' }}</span>
            </div>
        </div>
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

        {{-- ===== Convertir en colegio ===== --}}
        <div class="card p-4 mt-4" style="border-color:rgba(37,211,102,.45)">
            @if($quote->school_id)
                <h6 class="mb-2"><i class="bi bi-trophy" style="color:#25d366"></i> ¡Cliente ganado!</h6>
                <p class="small text-secondary mb-3">Ya es el colegio <b class="text-light">{{ $quote->schoolRecord?->name }}</b>.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.schools.edit', $quote->school_id) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-building"></i> Ver colegio</a>
                    <a href="{{ route('admin.contracts.create', ['school_id' => $quote->school_id]) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-plus"></i> Crear contrato</a>
                </div>
            @else
                <h6 class="mb-2"><i class="bi bi-building-add" style="color:#25d366"></i> ¿Te contrató? Conviértelo en colegio</h6>
                <p class="small text-secondary mb-3">Se crea el colegio con los datos de este cliente, la cotización queda como <b>Ganada</b> y pasas a crear el contrato.</p>
                <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" onsubmit="return confirm('¿Crear el colegio y marcar la cotización como ganada?')">@csrf
                    <label class="form-label small">Nombre del colegio</label>
                    <input name="school_name" value="{{ $quote->school }}" class="form-control form-control-sm mb-2" required maxlength="160">
                    <label class="form-label small">Ciudad</label>
                    <input name="city" class="form-control form-control-sm mb-3" maxlength="120" placeholder="Bogotá">
                    <button class="btn btn-success w-100" style="font-weight:700"><i class="bi bi-check2-circle"></i> Convertir en colegio e iniciar proceso</button>
                </form>
            @endif
        </div>

        {{-- ===== Historial de seguimiento ===== --}}
        <div class="card p-4 mt-4">
            <h6 class="mb-3"><i class="bi bi-clock-history"></i> Historial de seguimiento</h6>
            <form method="POST" action="{{ route('admin.quotes.note', $quote) }}" class="d-flex gap-2 mb-3">@csrf
                <input name="note" class="form-control form-control-sm" maxlength="255" placeholder="Ej.: Llamé, piden cotizar 45 estudiantes" required>
                <button class="btn btn-sm btn-outline-light text-nowrap"><i class="bi bi-plus"></i> Nota</button>
            </form>
            @forelse($quote->activities as $a)
                <div class="d-flex gap-3 pb-3 mb-3" style="border-bottom:1px solid var(--g-line)">
                    <span style="flex:0 0 32px;height:32px;border-radius:50%;display:grid;place-items:center;background:{{ $a->color() }}22;color:{{ $a->color() }}"><i class="bi {{ $a->icon() }}"></i></span>
                    <div class="small">
                        <div>{{ $a->description }}</div>
                        <div class="text-secondary">{{ $a->created_at->format('d/m/Y H:i') }}@if($a->user) · {{ $a->user->name }}@endif</div>
                    </div>
                </div>
            @empty
                <p class="small text-secondary mb-0">Aún no hay movimientos.</p>
            @endforelse
        </div>

        @if($others->count())
        <div class="card p-4 mt-4">
            <h6 class="mb-3"><i class="bi bi-person-lines-fill"></i> Otras cotizaciones de este cliente</h6>
            @foreach($others as $o)
                <a href="{{ route('admin.quotes.show', $o) }}" class="d-flex justify-content-between text-decoration-none small py-2" style="border-bottom:1px solid var(--g-line)">
                    <span>{{ $o->code() }} · {{ $o->created_at->format('d/m/Y') }}</span>
                    <span class="badge bg-{{ $o->statusColor() }}">{{ $o->statusLabel() }}</span>
                </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Recordatorios de pago')

@section('content')
@php
    use Illuminate\Support\Str;
    $wsvg = '<svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.1-1.7-.8-1.9-.9-.3-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.1.2-.3.2-.5.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.5-1.9-.2-.3 0-.4.1-.6l.4-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2.1-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 .9-1 2.3s1 2.7 1.2 2.9c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.7-.7 1.9-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3zM12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>';
@endphp

<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Recordatorios de pago</h3>
        <div class="text-secondary small">Recuerda al contacto de cada colegio cuántos estudiantes siguen pendientes. <b>No se menciona ningún valor</b>, solo la cantidad.</div>
    </div>
    @if($apiReady && $contracts->isNotEmpty())
        <form method="POST" action="{{ route('admin.reminders.all') }}" onsubmit="return confirm('¿Enviar el recordatorio por WhatsApp a TODOS los colegios con pendientes?')">
            @csrf
            <button class="btn btn-success btn-sm">{!! $wsvg !!} Enviar a todos ({{ $contracts->count() }})</button>
        </form>
    @endif
</div>

@if($errors->any())<div class="alert alert-danger mt-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($apiReady)
    <div class="alert mt-2 py-2 small" style="background:rgba(37,211,102,.12);border:1px solid var(--g-ok);color:var(--g-ok)">
        <i class="bi bi-check-circle"></i> API de WhatsApp Business <b>conectada</b>: puedes enviar automáticamente (botón «Enviar auto») sin abrir el chat.
    </div>
@else
    <div class="alert mt-2 py-2 small" style="background:var(--g-surface-2);border:1px solid var(--g-line);color:var(--g-muted)">
        <i class="bi bi-info-circle"></i> Modo manual (un clic abre WhatsApp con el mensaje listo). Para envío 100% automático, agrega tus llaves de WhatsApp Business en el archivo <code>.env</code> (<code>WHATSAPP_TOKEN</code>, <code>WHATSAPP_PHONE_ID</code>).
    </div>
@endif

{{-- Plantilla editable --}}
<div class="card p-3 mt-3">
    <form method="POST" action="{{ route('admin.reminders.template') }}">
        @csrf @method('PUT')
        <label class="form-label small mb-1">Mensaje del recordatorio (WhatsApp)</label>
        <textarea name="template" class="form-control" rows="3" style="font-size:14px">{{ $template }}</textarea>
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
            <div class="small text-secondary">
                Variables: <code>{contacto}</code> <code>{colegio}</code> <code>{codigo}</code> <code>{pendientes}</code> <code>{total}</code> <code>{marca}</code>
            </div>
            <button class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Guardar plantilla</button>
        </div>
    </form>
</div>

{{-- Lista de contratos con pendientes --}}
<div class="card p-3 mt-3">
    @if($contracts->isEmpty())
        <div class="text-center text-secondary py-4">
            <i class="bi bi-check2-circle" style="font-size:30px;color:var(--g-ok)"></i>
            <p class="mt-2 mb-0">¡Todo al día! No hay estudiantes con pago pendiente.</p>
        </div>
    @else
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>Colegio</th><th>Contrato</th><th class="text-center">Pendientes</th><th>Contacto</th><th>Último recordatorio</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($contracts as $c)
            @php
                $pend = $c->studentsPendingCount(); $tot = $c->studentsTotal();
                $phone = preg_replace('/\D/', '', (string) ($c->school->contact_phone ?? ''));
                if ($phone && ! Str::startsWith($phone, '57') && strlen($phone) <= 10) { $phone = '57'.$phone; }
                $msg = strtr($template, [
                    '{contacto}'   => $c->school->contact_name ?: 'equipo',
                    '{colegio}'    => $c->school->name ?? 'tu colegio',
                    '{codigo}'     => $c->code,
                    '{pendientes}' => $pend,
                    '{total}'      => $tot,
                    '{marca}'      => $brand,
                ]);
                $wa = $phone ? 'https://wa.me/'.$phone.'?text='.rawurlencode($msg) : null;
            @endphp
            <tr>
                <td>
                    <div class="fw-semibold">{{ $c->school->name ?? '—' }}</div>
                    <div class="small text-secondary">{{ $c->school->city }}</div>
                </td>
                <td><span class="badge bg-secondary">{{ $c->code }}</span></td>
                <td class="text-center">
                    <span class="badge bg-warning" style="font-size:13px">{{ $pend }}</span>
                    <span class="text-secondary small">/ {{ $tot }}</span>
                </td>
                <td>
                    @if($c->school->contact_name)<div class="small">{{ $c->school->contact_name }}</div>@endif
                    <div class="small text-secondary">{{ $c->school->contact_phone ?: 'Sin teléfono' }}</div>
                </td>
                <td class="small text-secondary" id="last-{{ $c->id }}">
                    {{ $c->payment_reminder_at ? $c->payment_reminder_at->diffForHumans() : '—' }}
                </td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @if($wa)
                            <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light"
                               onclick="markSent({{ $c->id }})" title="Abrir WhatsApp con el mensaje listo">{!! $wsvg !!} Manual</a>
                            @if($apiReady)
                                <form method="POST" action="{{ route('admin.reminders.auto', $c) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-success" title="Enviar automáticamente por la API">{!! $wsvg !!} Enviar auto</button>
                                </form>
                            @endif
                        @else
                            <span class="badge bg-secondary" title="Agrega un teléfono al colegio">Sin teléfono</span>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

<script>
    const RSENT = @json(route('admin.reminders.sent', ['contract' => 'CID']));
    const RCSRF = @json(csrf_token());
    function markSent(id){
        fetch(RSENT.replace('CID', id), { method:'POST', headers:{'X-CSRF-TOKEN':RCSRF,'Accept':'application/json'} })
            .then(r => r.json()).then(d => { if(d.ok){ const el=document.getElementById('last-'+id); if(el) el.textContent='hace un momento'; } })
            .catch(()=>{});
    }
</script>
@endsection

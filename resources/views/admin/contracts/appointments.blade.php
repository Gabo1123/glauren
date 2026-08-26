@extends('layouts.admin')
@section('title', 'Citas / Contacto')

@section('content')
<h3 class="mb-1"><i class="bi bi-calendar-check"></i> Citas y correo de contacto</h3>
<div class="text-secondary small mb-3">Reuniones que agendan desde la web y el correo automático que reciben los clientes.</div>

@unless($mailReady)
<div class="card p-3 mb-3" style="border-left:3px solid #e9b83f">
    <div class="small"><b>El correo (SMTP) aún no está configurado.</b> Agrega tu Gmail y la contraseña de aplicación en el <code>.env</code> (MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS) para que salgan los correos.</div>
</div>
@endunless

<div class="row g-3">
    {{-- Próximas citas --}}
    <div class="col-lg-7">
        <div class="card p-3">
            <h5 class="mb-3"><i class="bi bi-clock-history"></i> Próximas reuniones ({{ $upcoming->count() }})</h5>
            @forelse($upcoming as $a)
                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--g-line)">
                    <div>
                        <b>{{ \Carbon\Carbon::parse($a->date)->locale('es')->isoFormat('ddd D MMM') }} · {{ substr($a->time,0,5) }}</b><br>
                        <span>{{ $a->name }}</span>
                        @if($a->phone)<a href="https://wa.me/{{ preg_replace('/\D/','',$a->phone) }}" target="_blank" class="small ms-1" style="color:#25d366"><i class="bi bi-whatsapp"></i></a>@endif
                        @if($a->school)<span class="text-secondary small"> · {{ $a->school }}</span>@endif
                    </div>
                    <div class="d-flex gap-1">
                        <form method="POST" action="{{ route('admin.appointments.status', $a) }}">@csrf @method('PUT')<input type="hidden" name="status" value="atendida"><button class="btn btn-sm btn-outline-success" title="Marcar atendida"><i class="bi bi-check-lg"></i></button></form>
                        <form method="POST" action="{{ route('admin.appointments.status', $a) }}">@csrf @method('PUT')<input type="hidden" name="status" value="cancelada"><button class="btn btn-sm btn-outline-warning" title="Cancelar"><i class="bi bi-x-lg"></i></button></form>
                    </div>
                </div>
            @empty
                <div class="text-secondary small py-3">No hay reuniones agendadas.</div>
            @endforelse
        </div>

        @if($past->count())
        <div class="card p-3 mt-3">
            <h6 class="text-secondary mb-2">Historial</h6>
            @foreach($past as $a)
                <div class="d-flex justify-content-between align-items-center py-1 small">
                    <span>{{ \Carbon\Carbon::parse($a->date)->format('d/m') }} {{ substr($a->time,0,5) }} · {{ $a->name }}
                        <span class="badge {{ $a->status==='atendida'?'bg-success':'bg-secondary' }}">{{ $a->status }}</span>
                    </span>
                    <form method="POST" action="{{ route('admin.appointments.destroy', $a) }}" onsubmit="return confirm('¿Eliminar cita?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Configuración del correo --}}
    <div class="col-lg-5">
        <div class="card p-3">
            <h5 class="mb-3"><i class="bi bi-envelope-paper"></i> Correo automático al cliente</h5>
            <form method="POST" action="{{ route('admin.appointments.config') }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <label class="form-label small">Tu correo para recibir los avisos (leads)</label>
                <input type="email" name="contact_notify_email" class="form-control form-control-sm mb-3" value="{{ $cfg['notify_email'] }}" placeholder="tucorreo@gmail.com">

                <label class="form-label small">Asunto del correo al cliente</label>
                <input type="text" name="contact_reply_subject" class="form-control form-control-sm mb-3" value="{{ $cfg['subject'] }}" placeholder="¡Gracias por escribirnos! · Glauren">

                <label class="form-label small">Mensaje personalizado</label>
                <textarea name="contact_reply_message" class="form-control mb-3" rows="6" placeholder="Escribe el mensaje que recibirá el cliente…">{{ $cfg['message'] }}</textarea>

                <label class="form-label small">PDF a adjuntar (tu cotización/brochure)</label>
                @if($cfg['pdf'])
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="{{ Storage::url($cfg['pdf']) }}" target="_blank" class="badge bg-danger text-decoration-none"><i class="bi bi-file-pdf"></i> Ver PDF actual</a>
                        <form method="POST" action="{{ route('admin.appointments.pdf.remove') }}" onsubmit="return confirm('¿Quitar el PDF?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0">Quitar</button></form>
                    </div>
                @endif
                <input type="file" name="contact_pdf" class="form-control form-control-sm mb-3" accept="application/pdf">

                <button class="btn btn-warning fw-bold w-100"><i class="bi bi-check2-circle"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection

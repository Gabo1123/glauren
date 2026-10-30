@extends('layouts.admin')
@section('title', 'Leads / Contactos')

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Leads / Contactos</h3>
        <p class="small text-secondary mb-0">Personas que dejaron sus datos. Envíales la cotización y sigue cada cliente hasta convertirlo en colegio.</p>
    </div>
    <div class="d-flex gap-2">
        @if($needHuman)<span class="badge bg-warning text-dark" style="font-size:14px;padding:8px 12px"><i class="bi bi-hand-index-thumb"></i> {{ $needHuman }} piden asesor</span>@endif
        <span class="badge bg-secondary" style="font-size:14px;padding:8px 12px">{{ $leads->total() }} contactos</span>
    </div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Contacto</th><th>Colegio</th><th>Mensaje</th><th>Seguimiento</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @forelse($leads as $l)
                <tr>
                    <td>
                        {{ $l->name }}
                        @if($l->ai_paused)<br><span class="badge bg-warning text-dark mt-1"><i class="bi bi-person-raised-hand"></i> Pide asesor</span>@endif
                    </td>
                    <td>
                        @if($l->phone)<a href="https://wa.me/{{ preg_replace('/\D/','',$l->phone) }}" target="_blank" style="color:#25d366"><i class="bi bi-whatsapp"></i> {{ $l->phone }}</a><br>@endif
                        @if($l->email)<a href="mailto:{{ $l->email }}" class="small text-secondary">{{ $l->email }}</a>@endif
                    </td>
                    <td class="text-secondary small">{{ $l->school ?: '—' }}</td>
                    <td class="small" style="max-width:260px">{{ \Illuminate\Support\Str::limit($l->message ?: ($l->jacket_type ? 'Interés: '.$l->jacket_type : '—'), 90) }}</td>
                    <td style="white-space:nowrap">
                        @if($l->quote)
                            <a href="{{ route('admin.quotes.show', $l->quote) }}" class="text-decoration-none">
                                <span class="badge bg-{{ $l->quote->statusColor() }}">{{ $l->quote->statusLabel() }}</span>
                                <span class="small text-secondary d-block mt-1">{{ $l->quote->code() }}@if($l->quote->viewed_at) · <i class="bi bi-eye"></i>@endif</span>
                                @if($l->quote->needsFollowUp())<span class="small text-warning fw-bold d-block">Llamar hoy</span>@endif
                            </a>
                        @else
                            <form method="POST" action="{{ route('admin.leads.quote', $l) }}">@csrf
                                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-send"></i> Enviar cotización</button>
                            </form>
                        @endif
                        <span class="badge bg-secondary mt-1">{{ $l->source ?: 'web' }}</span>
                    </td>
                    <td class="text-secondary small">{{ $l->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end" style="white-space:nowrap">
                        @php $mc = $l->phone ? ($counts[$l->phone] ?? 0) : 0; @endphp
                        @if($mc)
                            <a href="{{ route('admin.leads.show', $l) }}" class="btn btn-sm btn-outline-success" title="Ver conversación"><i class="bi bi-chat-dots"></i> {{ $mc }}</a>
                        @endif
                        @if($l->source === 'whatsapp')
                            <form method="POST" action="{{ route('admin.leads.ai', $l) }}" class="d-inline">@csrf @method('PUT')
                                @if($l->ai_paused)
                                    <button class="btn btn-sm btn-success" title="Reactivar IA"><i class="bi bi-robot"></i> Reactivar IA</button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" title="Pausar IA y atender yo"><i class="bi bi-pause-circle"></i></button>
                                @endif
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.leads.destroy', $l) }}" class="d-inline" onsubmit="return confirm('¿Eliminar contacto?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">Aún no hay contactos. Aparecerán cuando alguien entre al diseñador dejando su correo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $leads->links() }}</div>
@endsection

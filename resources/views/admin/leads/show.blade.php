@extends('layouts.admin')
@section('title', 'Conversación')

@section('content')
<div class="g-topbar">
    <div>
        <a href="{{ route('admin.leads.index') }}" class="small text-secondary text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a contactos</a>
        <h3 class="mb-0 mt-1">{{ $lead->name ?: 'Contacto' }}</h3>
        <p class="small text-secondary mb-0">
            @if($lead->phone)<a href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}" target="_blank" style="color:#25d366"><i class="bi bi-whatsapp"></i> {{ $lead->phone }}</a>@endif
            @if($lead->school) · {{ $lead->school }}@endif
        </p>
    </div>
    <span class="badge bg-secondary" style="font-size:14px;padding:8px 12px">{{ $messages->count() }} mensajes</span>
</div>

@if($lead->source === 'whatsapp')
<div class="card p-3 mb-3" style="max-width:760px;{{ $lead->ai_paused ? 'border-left:3px solid #ffc107' : '' }}">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            @if($lead->ai_paused)
                <b class="text-warning"><i class="bi bi-person-raised-hand"></i> Este cliente pide asesor</b>
                <div class="small text-secondary">La IA está pausada{{ $lead->human_requested_at ? ' desde '.$lead->human_requested_at->diffForHumans() : '' }}. Responde tú por WhatsApp y luego reactiva la IA.</div>
            @else
                <b><i class="bi bi-robot"></i> IA activa</b>
                <div class="small text-secondary">La IA responde automáticamente a este cliente.</div>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($lead->phone)<a href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}" target="_blank" class="btn btn-sm" style="background:#25d366;color:#04240f;font-weight:600"><i class="bi bi-whatsapp"></i> Responder</a>@endif
            <form method="POST" action="{{ route('admin.leads.ai', $lead) }}">@csrf @method('PUT')
                @if($lead->ai_paused)
                    <button class="btn btn-sm btn-success"><i class="bi bi-robot"></i> Reactivar IA</button>
                @else
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pause-circle"></i> Pausar IA</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endif

<div class="card p-3" style="max-width:760px;background:#0b141a">
    @forelse($messages as $m)
        @if($m->direction === 'in')
            <div class="mb-2" style="max-width:78%">
                <div class="p-2 px-3" style="background:#202c33;color:#e9edef;border-radius:2px 12px 12px 12px">{!! nl2br(e($m->body)) !!}</div>
                <div class="small mt-1" style="color:#8696a0">{{ $m->created_at->format('d/m H:i') }}</div>
            </div>
        @else
            <div class="mb-2 ms-auto text-end" style="max-width:78%">
                <div class="p-2 px-3 text-start" style="background:#005c4b;color:#e9edef;border-radius:12px 2px 12px 12px">{!! nl2br(e($m->body)) !!}</div>
                <div class="small mt-1" style="color:#8696a0"><i class="bi bi-robot"></i> IA · {{ $m->created_at->format('d/m H:i') }}</div>
            </div>
        @endif
    @empty
        <div class="text-center py-4" style="color:#8696a0">Aún no hay mensajes con este contacto.</div>
    @endforelse
</div>

<div class="small text-secondary mt-3" style="max-width:760px">
    <i class="bi bi-info-circle"></i> Todo el historial queda guardado. La IA lee los últimos mensajes para no repetir preguntas y continuar la conversación donde iba.
</div>
@endsection

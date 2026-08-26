@extends('layouts.admin')
@section('title', 'Difusión')

@section('content')
<h3 class="mb-1"><i class="bi bi-megaphone"></i> Difusión por WhatsApp</h3>
<div class="text-secondary small mb-3" style="max-width:820px">
    Envía una promo, noticia o recordatorio a muchos contactos de una vez.
</div>

@if($errors->any())
    <div class="g-badge g-badge--warn" style="margin-bottom:14px;padding:10px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

@unless($apiReady)
<div class="card p-3 mb-3" style="max-width:820px;border-left:3px solid #e9b83f">
    <div class="small"><b>WhatsApp no está configurado.</b> Agrega las llaves en tu <code>.env</code> para poder enviar.</div>
</div>
@endunless

<div class="card p-4" style="max-width:820px">
    <form method="POST" action="{{ route('admin.broadcast.send') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label"><b>¿A quién?</b></label>
            @foreach($audiences as $key => $label)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="audience" id="aud_{{ $key }}" value="{{ $key }}" {{ old('audience', 'leads_wa') === $key ? 'checked' : '' }}>
                    <label class="form-check-label" for="aud_{{ $key }}">
                        {{ $label }} <span class="badge bg-secondary ms-1">{{ $counts[$key] }} contacto(s)</span>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label"><b>Mensaje</b></label>
            <textarea name="message" class="form-control" rows="5" maxlength="1000"
                placeholder="Ej: 🎉 ¡Promo de temporada! El primer colegio en contratar su promoción se lleva una chaqueta GRATIS. Escríbenos y diseña la tuya en 3D: chaquetasdeprom.com/personalizar">{{ old('message') }}</textarea>
            <div class="small text-secondary mt-1">Texto simple. Evita precios. Máx 1000 caracteres.</div>
        </div>

        <button class="btn btn-warning fw-bold" {{ $apiReady ? '' : 'disabled' }}
            onclick="return confirm('¿Enviar esta difusión por WhatsApp?')">
            <i class="bi bi-send"></i> Enviar difusión
        </button>
    </form>
</div>

<div class="card p-3 mt-3" style="max-width:820px;border-left:3px solid #6c757d">
    <div class="small text-secondary">
        <i class="bi bi-info-circle"></i> <b>Regla de Meta:</b> el texto libre solo llega a quien te escribió en las últimas <b>24 horas</b>.
        Para anuncios a contactos fríos se necesitan <b>plantillas aprobadas</b> por WhatsApp (se configuran aparte). Todo envío queda registrado en cada conversación.
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Configuración (llaves API)')

@section('content')
<h3 class="mb-1"><i class="bi bi-key"></i> Configuración de llaves (API)</h3>
<div class="text-secondary small mb-3" style="max-width:760px">
    Pega aquí tus llaves. Lo que escribas queda <b>oculto</b> (●●●●) y se guarda de forma segura.
    Deja un campo en blanco para <b>no cambiarlo</b>.
</div>

<form method="POST" action="{{ route('admin.apikeys.update') }}" style="max-width:760px">
    @csrf @method('PUT')

    @foreach($groups as $groupName => $fields)
    <div class="card p-4 mb-3">
        <h5 class="mb-3">{{ $groupName }}</h5>
        @foreach($fields as $key => $meta)
            @php $isSecret = in_array($key, $secret, true); @endphp
            <div class="mb-3">
                <label class="form-label small mb-1 d-flex align-items-center gap-2">
                    {{ $meta[0] }}
                    @if($isSet[$key])
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> Configurada</span>
                    @else
                        <span class="badge bg-secondary">Sin configurar</span>
                    @endif
                </label>

                @if($isSecret)
                    <div class="input-group input-group-sm">
                        <input type="password" name="keys[{{ $key }}]" class="form-control js-secret" autocomplete="new-password"
                               placeholder="{{ $isSet[$key] ? '●●●●●●●● (ya guardada — deja vacío para conservarla)' : 'Pega tu llave aquí' }}">
                        <button type="button" class="btn btn-outline-secondary js-toggle" tabindex="-1" title="Ver/ocultar"><i class="bi bi-eye"></i></button>
                    </div>
                    @if($isSet[$key])
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="clear_{{ $key }}" value="1" id="clr_{{ $key }}">
                            <label class="form-check-label small text-danger" for="clr_{{ $key }}">Borrar esta llave</label>
                        </div>
                    @endif
                @else
                    <input type="text" name="keys[{{ $key }}]" class="form-control form-control-sm" value="{{ $visible[$key] }}" placeholder="{{ $meta[1] }}">
                @endif

                <div class="small text-secondary mt-1">{{ $meta[1] }}</div>
            </div>
        @endforeach
    </div>
    @endforeach

    <div class="card p-4 mb-3" style="border-left:3px solid #25d366">
        <h5 class="mb-3"><i class="bi bi-shield-lock"></i> Seguridad · Verificación en dos pasos (2FA)</h5>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="twofaSw" name="admin_2fa_enabled" value="1" {{ $twofa['enabled'] ? 'checked' : '' }}>
            <label class="form-check-label" for="twofaSw">
                Pedir un <b>código por WhatsApp</b> cada vez que alguien entra al panel (además de la contraseña)
            </label>
        </div>
        <label class="form-label small mb-1">Número de WhatsApp que recibe los códigos</label>
        <input type="text" name="admin_2fa_phone" class="form-control form-control-sm" value="{{ $twofa['phone'] }}" placeholder="Ej: 3183504386 o 573183504386" style="max-width:320px">
        <div class="small text-secondary mt-2">
            <i class="bi bi-info-circle"></i> Requiere que WhatsApp esté configurado (arriba). El código llega a este número y vence en 5 minutos.
        </div>
    </div>

    <button class="btn btn-warning fw-bold"><i class="bi bi-check2-circle"></i> Guardar y aplicar</button>
</form>

<script>
document.querySelectorAll('.js-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
        var inp = btn.closest('.input-group').querySelector('input');
        var ic = btn.querySelector('i');
        if(inp.type === 'password'){ inp.type='text'; ic.className='bi bi-eye-slash'; }
        else { inp.type='password'; ic.className='bi bi-eye'; }
    });
});
</script>
@endsection

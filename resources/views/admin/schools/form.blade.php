@extends('layouts.admin')
@section('title', $school->exists ? 'Editar colegio' : 'Nuevo colegio')

@section('content')
@php $isNew = ! $school->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nuevo colegio' : 'Editar: '.$school->name }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:620px">
    <form method="POST" action="{{ $isNew ? route('admin.schools.store') : route('admin.schools.update', $school) }}">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="mb-3"><label class="form-label small">Nombre del colegio</label><input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required></div>
        <div class="mb-3"><label class="form-label small">Ciudad</label><input type="text" name="city" class="form-control" value="{{ old('city', $school->city) }}"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Contacto (nombre)</label><input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $school->contact_name) }}"></div>
            <div class="col-md-6"><label class="form-label small">Teléfono</label><input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $school->contact_phone) }}"></div>
            <div class="col-md-12"><label class="form-label small">Correo</label><input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $school->contact_email) }}"></div>
        </div>
        <div class="mb-3 mt-3">
            <label class="form-label small">¿Quién paga las chaquetas?</label>
            @php $pm = old('payment_mode', $school->payment_mode ?? 'estudiante'); @endphp
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_mode" value="estudiante" id="pm_est" {{ $pm === 'estudiante' ? 'checked' : '' }}>
                <label class="form-check-label" for="pm_est"><b>Cada estudiante paga su parte</b> — cada alumno paga en línea con su documento.</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_mode" value="colegio" id="pm_col" {{ $pm === 'colegio' ? 'checked' : '' }}>
                <label class="form-check-label" for="pm_col"><b>El colegio paga todo</b> — los estudiantes no pagan en línea (lo gestiona el colegio).</label>
            </div>
        </div>

        <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $school->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Activo</label>
        </div>

        <hr class="my-4" style="border-color:var(--g-line)">
        <div class="d-flex align-items-center gap-2 mb-1"><i class="bi bi-key text-warning"></i> <strong>Acceso al portal del colegio</strong></div>
        <p class="small text-secondary mb-3">Con estos datos el colegio entra en <code>/colegio</code> para ver su contrato, avance, fotos y alumnos.
            @if($school->exists && $school->user)<span class="text-success d-block mt-1"><i class="bi bi-check-circle"></i> Cuenta activa: {{ $school->user->email }}</span>@endif
        </p>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Correo de acceso</label><input type="email" name="login_email" class="form-control" value="{{ old('login_email', $school->user->email ?? $school->contact_email) }}" placeholder="colegio@correo.com"></div>
            <div class="col-md-6"><label class="form-label small">Contraseña {{ $school->exists && $school->user ? '(dejar en blanco para no cambiar)' : '' }}</label><input type="text" name="login_password" class="form-control" placeholder="{{ $school->exists && $school->user ? '••••••' : 'Mínimo 6 caracteres' }}"></div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.schools.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

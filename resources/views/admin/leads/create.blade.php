@extends('layouts.admin')
@section('title', 'Nuevo contacto')

@section('content')
<a href="{{ route('admin.leads.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a Leads</a>
<h3 class="my-3">Nuevo contacto</h3>
<p class="small text-secondary">Para clientes que te llegan directo (llamada, WhatsApp, referido). Se crea el contacto con su cotización y pasas de una vez a enviársela.</p>

@if($errors->any())
    <div class="alert alert-danger small">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.leads.store') }}" class="row g-4">
    @csrf
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="mb-3">Datos del contacto</h6>
            <label class="form-label small">Nombre *</label>
            <input name="name" value="{{ old('name') }}" class="form-control mb-3" required maxlength="120" autofocus>
            <label class="form-label small">Celular / WhatsApp *</label>
            <input name="phone" value="{{ old('phone') }}" class="form-control mb-3" required maxlength="40" placeholder="3001234567" inputmode="tel">
            <label class="form-label small">Correo</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control mb-2" maxlength="160">
            <div class="form-check mb-3">
                <input type="hidden" name="send_email" value="0">
                <input class="form-check-input" type="checkbox" name="send_email" value="1" id="send_email" {{ old('send_email', '1') ? 'checked' : '' }}>
                <label class="form-check-label small" for="send_email">Enviarle la cotización por correo al crearla</label>
            </div>
            <label class="form-label small">Colegio</label>
            <input name="school" value="{{ old('school') }}" class="form-control mb-3" maxlength="160">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small">Estudiantes *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 30) }}" min="1" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label small">Llamar el</label>
                    <input type="date" name="follow_up_at" value="{{ old('follow_up_at', today()->toDateString()) }}" class="form-control">
                </div>
            </div>
            <label class="form-label small mt-3">Notas (cómo llegó, qué busca…)</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h6 class="mb-1">¿Qué le cotizas? <span class="small text-secondary fw-normal">(opcional)</span></h6>
            <p class="small text-secondary">Si no eliges nada, recibe la cotización completa con todos los precios y combos.</p>
            @include('admin.quotes.partials.items')
            <button class="btn btn-primary w-100"><i class="bi bi-person-plus"></i> Crear contacto y cotización</button>
        </div>
    </div>
</form>
@endsection

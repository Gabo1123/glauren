@extends('layouts.admin')
@section('title', 'Nueva cotización')

@section('content')
<a href="{{ route('admin.quotes.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
<h3 class="my-3">Nueva cotización</h3>
<p class="small text-secondary">Se le asigna un número consecutivo y un enlace con el diseño G'Lauren que puedes enviar por WhatsApp.</p>

@if($errors->any())
    <div class="alert alert-danger small">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('admin.quotes.store') }}" class="row g-4">
    @csrf
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="mb-3">Cliente</h6>
            <label class="form-label small">Nombre *</label>
            <input name="customer_name" value="{{ old('customer_name') }}" class="form-control mb-3" required maxlength="120">
            <label class="form-label small">Celular / WhatsApp *</label>
            <input name="customer_phone" value="{{ old('customer_phone') }}" class="form-control mb-3" required maxlength="40" placeholder="3001234567">
            <label class="form-label small">Correo</label>
            <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="form-control mb-3" maxlength="160">
            <label class="form-label small">Colegio</label>
            <input name="school" value="{{ old('school') }}" class="form-control mb-3" maxlength="160">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small">Estudiantes *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 30) }}" min="1" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label small">Próximo seguimiento</label>
                    <input type="date" name="follow_up_at" value="{{ old('follow_up_at', today()->addDays(2)->toDateString()) }}" class="form-control">
                </div>
            </div>
            <label class="form-label small mt-3">Notas internas</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h6 class="mb-3">¿Qué le cotizas?</h6>
            @include('admin.quotes.partials.items')
            <button class="btn btn-primary w-100"><i class="bi bi-file-earmark-plus"></i> Crear cotización</button>
        </div>
    </div>
</form>
@endsection

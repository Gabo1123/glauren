@extends('layouts.admin')
@section('title', $contract->exists ? 'Editar contrato' : 'Nuevo contrato')

@section('content')
@php $isNew = ! $contract->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nuevo contrato' : 'Editar contrato '.$contract->code }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($schools->isEmpty())
    <div class="alert alert-warning">Primero crea un <a href="{{ route('admin.schools.create') }}">colegio</a>.</div>
@endif

<div class="card p-4" style="max-width:700px">
    <form method="POST" action="{{ $isNew ? route('admin.contracts.store') : route('admin.contracts.update', $contract) }}">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label small">Colegio</label>
                <select name="school_id" class="form-select" required>
                    <option value="">— Elegir —</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ old('school_id', $contract->school_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small">Año</label><input type="number" name="year" class="form-control" value="{{ old('year', $contract->year) }}"></div>

            <div class="col-md-6">
                <label class="form-label small">Tipo de chaqueta</label>
                <select name="jacket_type_id" class="form-select">
                    <option value="">— (opcional) —</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('jacket_type_id', $contract->jacket_type_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label small">Código (opcional — se genera solo)</label><input type="text" name="code" class="form-control" value="{{ old('code', $contract->code) }}" placeholder="CTR-{{ date('Y') }}-001"></div>

            <div class="col-md-6"><label class="form-label small">Precio por unidad</label><input type="number" step="0.01" name="price_per_unit" class="form-control" value="{{ old('price_per_unit', $contract->price_per_unit) }}"></div>
            <div class="col-md-6">
                <label class="form-label small">Estado de fabricación</label>
                <select name="production_status" class="form-select">
                    @foreach($stages as $k=>$label)<option value="{{ $k }}" {{ old('production_status', $contract->production_status)==$k?'selected':'' }}>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-12"><label class="form-label small">Notas internas</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $contract->notes) }}</textarea></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

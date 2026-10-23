@extends('foto.layout')
@section('title', ($package->exists ? 'Editar' : 'Nuevo').' paquete')

@section('content')
@php $isNew = ! $package->exists; @endphp
<a href="{{ route('foto.dashboard') }}" class="text-muted2 text-decoration-none small"><i class="bi bi-arrow-left"></i> Volver al panel</a>
<h4 class="my-3">{{ $isNew ? 'Nuevo paquete' : 'Editar: '.$package->name }}</h4>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ $isNew ? route('foto.packages.store') : route('foto.packages.update', $package) }}">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nombre del paquete *</label><input name="name" class="form-control" value="{{ old('name', $package->name) }}" required></div>
            <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="featured" id="f" value="1" {{ old('featured', $package->featured ?? false) ? 'checked' : '' }}><label class="form-check-label" for="f">Destacado</label></div></div>
            <div class="col-12"><label class="form-label">Frase corta</label><input name="tagline" class="form-control" value="{{ old('tagline', $package->tagline) }}"></div>
            <div class="col-12">
                <label class="form-label">Qué incluye (una línea por ítem)</label>
                <textarea name="features" class="form-control" rows="7">{{ old('features', is_array($package->features) ? implode("\n", $package->features) : '') }}</textarea>
                <div class="text-muted2 mt-1" style="font-size:12px">Sin precios. Cada línea aparece como un ✔ en la página.</div>
            </div>
            <div class="col-md-3"><label class="form-label">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $package->sort ?? 0) }}"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="active" id="a" value="1" {{ old('active', $package->active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="a">Visible</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-gold"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('foto.dashboard') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

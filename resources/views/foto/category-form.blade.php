@extends('foto.layout')
@section('title', ($category->exists ? 'Editar' : 'Nueva').' categoría')

@section('content')
@php $isNew = ! $category->exists; @endphp
<a href="{{ route('foto.dashboard') }}" class="text-muted2 text-decoration-none small"><i class="bi bi-arrow-left"></i> Volver al panel</a>
<h4 class="my-3">{{ $isNew ? 'Nueva categoría' : 'Editar: '.$category->name }}</h4>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ $isNew ? route('foto.categories.store') : route('foto.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nombre *</label><input name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Ícono (emoji)</label><input name="icon" class="form-control" value="{{ old('icon', $category->icon) }}" placeholder="📷"></div>
            <div class="col-12"><label class="form-label">Subtítulo</label><input name="subtitle" class="form-control" value="{{ old('subtitle', $category->subtitle) }}"></div>
            <div class="col-12"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea></div>
            <div class="col-12"><label class="form-label">Palabras clave (para Google, separadas por coma)</label><textarea name="keywords" class="form-control" rows="2">{{ old('keywords', $category->keywords) }}</textarea></div>
            <div class="col-md-8"><label class="form-label">Imagen (opcional)</label><input type="file" name="image" class="form-control" accept="image/*">
                @if($category->imageUrl())<img src="{{ $category->imageUrl() }}" style="height:60px;border-radius:8px;margin-top:8px">@endif
            </div>
            <div class="col-md-2"><label class="form-label">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $category->sort ?? 0) }}"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="active" id="a" value="1" {{ old('active', $category->active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="a">Visible</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-gold"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('foto.dashboard') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

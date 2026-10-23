@extends('foto.layout')
@section('title', ($service->exists ? 'Editar' : 'Nuevo').' servicio')

@section('content')
@php $isNew = ! $service->exists; @endphp
<a href="{{ route('foto.dashboard') }}" class="text-muted2 text-decoration-none small"><i class="bi bi-arrow-left"></i> Volver al panel</a>
<h4 class="my-3">{{ $isNew ? 'Nuevo servicio' : 'Editar servicio' }} <span class="text-muted2" style="font-size:15px">· {{ $category->name }}</span></h4>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ $isNew ? route('foto.services.store', $category) : route('foto.services.update', $service) }}" enctype="multipart/form-data">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="row g-3">
            <div class="col-12"><label class="form-label">Título *</label><input name="title" class="form-control" value="{{ old('title', $service->title) }}" required></div>
            <div class="col-12"><label class="form-label">Subtítulo</label><input name="subtitle" class="form-control" value="{{ old('subtitle', $service->subtitle) }}"></div>
            <div class="col-12"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea></div>
            <div class="col-12"><label class="form-label">Palabras clave (para Google)</label><textarea name="keywords" class="form-control" rows="2">{{ old('keywords', $service->keywords) }}</textarea></div>
            <div class="col-md-8"><label class="form-label">Imagen (opcional)</label><input type="file" name="image" class="form-control" accept="image/*">
                @if($service->imageUrl())<img src="{{ $service->imageUrl() }}" style="height:60px;border-radius:8px;margin-top:8px">@endif
            </div>
            <div class="col-md-2"><label class="form-label">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $service->sort ?? 0) }}"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="active" id="a" value="1" {{ old('active', $service->active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="a">Visible</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-gold"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('foto.dashboard') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

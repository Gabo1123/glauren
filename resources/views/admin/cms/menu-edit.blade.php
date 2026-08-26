@extends('layouts.admin')
@section('title', 'Editar ítem de menú')

@section('content')
<h3 class="mb-4">Editar ítem</h3>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card p-4" style="max-width:520px">
    <form method="POST" action="{{ route('admin.menu.update', $menu) }}">
        @csrf @method('PUT')
        <div class="mb-2"><label class="form-label small">Nombre</label><input type="text" name="label" class="form-control" value="{{ old('label', $menu->label) }}" required></div>
        <div class="mb-2"><label class="form-label small">Enlace</label><input type="text" name="url" class="form-control" value="{{ old('url', $menu->url) }}" required></div>
        <div class="mb-2"><label class="form-label small">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $menu->sort) }}"></div>
        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $menu->active ? 'checked' : '' }} id="a"><label class="form-check-label small" for="a">Activo</label></div>
        <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
        <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-light">Cancelar</a>
    </form>
</div>
@endsection

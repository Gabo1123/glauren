@extends('layouts.admin')
@section('title', $page->exists ? 'Editar página' : 'Nueva página')

@section('content')
@php $isNew = ! $page->exists; @endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="mb-0">{{ $isNew ? 'Nueva página' : 'Editar: '.$page->title }}</h3>
    @unless($isNew)
        <a href="{{ route('admin.blocks.index', ['page' => $page->id]) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-grid-1x2"></i> Módulos de esta página</a>
    @endunless
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:820px">
    <form method="POST" action="{{ $isNew ? route('admin.pages.store') : route('admin.pages.update', $page) }}">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required></div>
        <div class="mb-3"><label class="form-label small">URL (slug) — opcional</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="se genera del título"></div>
        <div class="mb-3"><label class="form-label small">Contenido (permite HTML)</label><textarea name="body" class="form-control" rows="12">{{ old('body', $page->body) }}</textarea></div>
        <div class="row g-3 align-items-end">
            <div class="col-4"><label class="form-label small">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $page->sort) }}"></div>
            <div class="col-8"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="published" value="1" {{ old('published', $page->published) ? 'checked' : '' }} id="pub"><label class="form-check-label" for="pub">Publicada</label></div></div>
        </div>
        @include('admin.cms._seo_panel', ['model' => $page, 'type' => 'page'])

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

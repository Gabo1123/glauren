@extends('layouts.admin')
@section('title', $post->exists ? 'Editar noticia' : 'Nueva noticia')

@section('content')
@php $isNew = ! $post->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nueva noticia' : 'Editar: '.$post->title }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:820px">
    <form method="POST" action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}" enctype="multipart/form-data">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required></div>
        <div class="mb-3"><label class="form-label small">URL (slug) — opcional</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $post->slug) }}"></div>
        <div class="mb-3"><label class="form-label small">Resumen</label><textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt', $post->excerpt) }}</textarea></div>
        <div class="row g-2 mb-3">
            <div class="col-md-6"><label class="form-label small">Autor</label><input type="text" name="author" class="form-control" value="{{ old('author', $post->author) }}" placeholder="Equipo Glauren"></div>
            <div class="col-md-6"><label class="form-label small">Cargo del autor</label><input type="text" name="author_role" class="form-control" value="{{ old('author_role', $post->author_role) }}" placeholder="Expertos en chaquetas de promoción"></div>
        </div>
        <div class="mb-3"><label class="form-label small">Contenido (permite HTML)</label><textarea name="body" class="form-control" rows="10">{{ old('body', $post->body) }}</textarea></div>
        <div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label small">Imagen de portada</label><input type="file" name="cover" class="form-control" accept="image/*">
                @if(!$isNew && $post->coverUrl())<img src="{{ $post->coverUrl() }}" class="mt-2 rounded" style="max-height:80px">@endif</div>
            <div class="col-md-4"><label class="form-label small">Fecha de publicación</label><input type="date" name="published_at" class="form-control" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d')) }}"></div>
            <div class="col-md-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', $post->active) ? 'checked' : '' }} id="act"><label class="form-check-label" for="act">Activa</label></div></div>
        </div>
        @include('admin.cms._seo_panel', ['model' => $post, 'type' => 'post'])

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

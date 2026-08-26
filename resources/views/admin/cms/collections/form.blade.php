@extends('layouts.admin')
@section('title', $collection->exists ? 'Editar colección' : 'Nueva colección')

@section('content')
@php $isNew = ! $collection->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nueva colección' : 'Editar: '.$collection->title }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:680px">
    <form method="POST" action="{{ $isNew ? route('admin.collections.store') : route('admin.collections.update', $collection) }}" enctype="multipart/form-data" id="colForm">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="mb-3"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" value="{{ old('title', $collection->title) }}" required></div>

        <div class="mb-3">
            <label class="form-label small">Descripción (se muestra en la página de la colección)</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Cuéntale al cliente de qué material es este estilo, para qué sirve, detalles…">{{ old('description', $collection->description) }}</textarea>
        </div>

        <div class="mb-3"><label class="form-label small">Enlace personalizado — opcional</label><input type="text" name="link" class="form-control" value="{{ old('link', $collection->link) }}" placeholder="Déjalo vacío para abrir la galería de esta colección"></div>

        <hr class="my-4" style="border-color:var(--g-line)">
        <div class="mb-2"><strong><i class="bi bi-image text-warning"></i> Imagen de portada</strong> <span class="small text-secondary">(la que se ve en el home)</span></div>
        <div class="row g-3 mb-2">
            <div class="col-md-6"><label class="form-label small">Subir archivo</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <div class="col-md-6"><label class="form-label small">…o pegar URL</label><input type="text" name="image_url" class="form-control" placeholder="https://…"></div>
        </div>
        @if($collection->image_path)
            <div class="mb-3"><img src="{{ $collection->imageUrl() }}" style="max-height:130px;border-radius:8px;border:1px solid var(--g-line)"></div>
        @endif

        <hr class="my-4" style="border-color:var(--g-line)">
        <div class="mb-2"><strong><i class="bi bi-images text-warning"></i> Galería de la colección</strong> <span class="small text-secondary">(fotos que ve el cliente al entrar)</span></div>

        @if(! $isNew && $collection->images->count())
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($collection->images as $img)
                    <div style="position:relative">
                        <img src="{{ $img->url() }}" style="width:96px;height:120px;object-fit:cover;border-radius:8px;border:1px solid var(--g-line)">
                        <button type="submit" form="delImg{{ $img->id }}" title="Eliminar" style="position:absolute;top:-8px;right:-8px;background:#c0392b;color:#fff;border:none;width:24px;height:24px;border-radius:50%;cursor:pointer;line-height:1"><i class="bi bi-x"></i></button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="row g-3 mb-2">
            <div class="col-md-6"><label class="form-label small">Subir varias fotos</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
            <div class="col-md-6"><label class="form-label small">…o pegar URLs (una por línea)</label><textarea name="image_urls" class="form-control" rows="2" placeholder="https://…&#10;https://…"></textarea></div>
        </div>

        <hr class="my-4" style="border-color:var(--g-line)">
        <div class="row g-3 align-items-end">
            <div class="col-4"><label class="form-label small">Orden</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $collection->sort) }}"></div>
            <div class="col-8"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $collection->active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="active">Activa (visible en el sitio)</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-light">Cancelar</a>
            @unless($isNew)<a href="{{ route('collection.show', $collection->slug) }}" target="_blank" class="btn btn-outline-light"><i class="bi bi-box-arrow-up-right"></i> Ver galería</a>@endunless
        </div>
    </form>
</div>

{{-- Formularios de borrado de imágenes (fuera del form principal) --}}
@unless($isNew)
    @foreach($collection->images as $img)
        <form id="delImg{{ $img->id }}" method="POST" action="{{ route('admin.collections.images.destroy', [$collection, $img]) }}">@csrf @method('DELETE')</form>
    @endforeach
@endunless

<script>
// Vista previa de las imágenes elegidas antes de guardar
document.querySelectorAll('#colForm input[type=file]').forEach(function(inp){
    var box=document.createElement('div');
    box.className='d-flex flex-wrap gap-2 mt-2';
    inp.insertAdjacentElement('afterend', box);
    inp.addEventListener('change', function(){
        box.innerHTML='';
        Array.prototype.slice.call(inp.files, 0, 24).forEach(function(f){
            if(!f.type || f.type.indexOf('image/')!==0) return;
            var img=document.createElement('img');
            img.src=URL.createObjectURL(f);
            img.style.cssText='width:84px;height:104px;object-fit:cover;border-radius:8px;border:1px solid var(--g-line)';
            img.onload=function(){ URL.revokeObjectURL(img.src); };
            box.appendChild(img);
        });
    });
});
</script>
@endsection

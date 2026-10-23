@extends('foto.layout')
@section('title', 'Panel · Fotografía de Grados')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h4 class="mb-0">Categorías y servicios</h4>
    <a href="{{ route('foto.categories.create') }}" class="btn btn-gold btn-sm"><i class="bi bi-plus-lg"></i> Nueva categoría</a>
</div>

@forelse($categories as $cat)
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
                <div style="font-size:18px;font-weight:700">{{ $cat->icon }} {{ $cat->name }}
                    @unless($cat->active)<span class="chip">oculta</span>@endunless
                </div>
                @if($cat->subtitle)<div class="text-muted2" style="font-size:14px">{{ $cat->subtitle }}</div>@endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('foto.services.create', $cat) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-plus"></i> Servicio</a>
                <a href="{{ route('foto.categories.edit', $cat) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('foto.categories.destroy', $cat) }}" onsubmit="return confirm('¿Eliminar la categoría y sus servicios?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </div>
        </div>

        <div class="mt-3">
            @forelse($cat->services as $srv)
                <div class="srv">
                    <div>
                        <b>{{ $srv->title }}</b> @unless($srv->active)<span class="chip">oculto</span>@endunless
                        @if($srv->subtitle)<div class="text-muted2" style="font-size:13px">{{ $srv->subtitle }}</div>@endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('foto.services.edit', $srv) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('foto.services.destroy', $srv) }}" onsubmit="return confirm('¿Eliminar servicio?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button></form>
                    </div>
                </div>
            @empty
                <div class="text-muted2" style="font-size:13px">Sin servicios todavía. Agrega el primero con el botón “Servicio”.</div>
            @endforelse
        </div>
    </div>
@empty
    <div class="card p-4 text-center text-muted2">Aún no hay categorías. Crea la primera.</div>
@endforelse

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 mb-3">
    <h4 class="mb-0">Galería de muestras</h4>
    <span class="text-muted2" style="font-size:13px">{{ $gallery->count() }} foto(s)</span>
</div>
<div class="card p-3 mb-3">
    <form method="POST" action="{{ route('foto.gallery.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Fotos (puedes elegir varias)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Categoría (para los filtros)</label>
            <select name="photo_category_id" class="form-select">
                <option value="">— General —</option>
                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->icon }} {{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-gold w-100"><i class="bi bi-upload"></i> Subir fotos</button></div>
    </form>
    @if($gallery->count())
        <div class="row g-2 mt-2">
            @foreach($gallery as $img)
                <div class="col-4 col-md-2">
                    <div style="position:relative">
                        <img src="{{ $img->url() }}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                        @if($img->category)<span class="chip" style="position:absolute;left:4px;bottom:4px;font-size:10px">{{ $img->category->name }}</span>@endif
                        <form method="POST" action="{{ route('foto.gallery.destroy', $img) }}" style="position:absolute;top:4px;right:4px" onsubmit="return confirm('¿Eliminar foto?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger py-0 px-1"><i class="bi bi-x"></i></button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted2 mt-2 mb-0" style="font-size:13px">Sube fotos de ejemplo para que los clientes vean tu trabajo. Elige la categoría para que aparezcan en los filtros de la página.</p>
    @endif
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 mb-3">
    <h4 class="mb-0">Paquetes de grado</h4>
    <a href="{{ route('foto.packages.create') }}" class="btn btn-gold btn-sm"><i class="bi bi-plus-lg"></i> Nuevo paquete</a>
</div>

<div class="row g-3">
    @forelse($packages as $pkg)
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="font-weight:700">{{ $pkg->name }} @if($pkg->featured)<span class="chip" style="color:var(--gold)">destacado</span>@endif</div>
                </div>
                @if($pkg->tagline)<div class="text-muted2" style="font-size:13px">{{ $pkg->tagline }}</div>@endif
                <ul class="mt-2 ps-3" style="font-size:13px">
                    @foreach(($pkg->features ?? []) as $f)<li>{{ $f }}</li>@endforeach
                </ul>
                <div class="d-flex gap-2 mt-auto pt-2">
                    <a href="{{ route('foto.packages.edit', $pkg) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i> Editar</a>
                    <form method="POST" action="{{ route('foto.packages.destroy', $pkg) }}" onsubmit="return confirm('¿Eliminar paquete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card p-4 text-center text-muted2">Sin paquetes.</div></div>
    @endforelse
</div>

<p class="text-muted2 mt-4" style="font-size:12px"><i class="bi bi-info-circle"></i> Recuerda: aquí no se muestran precios al cliente; cada paquete lleva a “Pedir cotización” por WhatsApp en la página pública.</p>
@endsection

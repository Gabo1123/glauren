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

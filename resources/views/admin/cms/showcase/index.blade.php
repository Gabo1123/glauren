@extends('layouts.admin')
@section('title', 'Promociones (galería del home)')

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Promociones felices</h3>
        <p class="small text-secondary mb-0">Las fotos de la sección “Ya visten Glauren” del inicio. La 1ª sale grande.</p>
    </div>
    <a href="{{ route('home') }}#gal" target="_blank" class="btn btn-outline-light"><i class="bi bi-box-arrow-up-right"></i> Ver en el sitio</a>
</div>

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

{{-- Agregar fotos --}}
<div class="card p-4 mb-4" style="max-width:820px">
    <form method="POST" action="{{ route('admin.showcase.store') }}" enctype="multipart/form-data" id="showcaseForm">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label small">Subir fotos (varias a la vez)</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
            <div class="col-md-4"><label class="form-label small">…o pegar una URL</label><input type="text" name="image_url" class="form-control" placeholder="https://…"></div>
            <div class="col-md-3"><label class="form-label small">Etiqueta (colegio)</label><input type="text" name="label" class="form-control" placeholder="Colegio La Giralda"></div>
        </div>
        <button class="btn btn-primary mt-3"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
</div>

{{-- Galería actual --}}
@if($photos->isEmpty())
    <div class="card p-5 text-center text-secondary">Aún no hay fotos. Agrega las primeras arriba.</div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
        @foreach($photos as $p)
            <div class="card p-2">
                <div style="position:relative;aspect-ratio:16/9;border-radius:8px;overflow:hidden;border:1px solid var(--g-line)">
                    <img src="{{ $p->imageUrl() }}" alt="{{ $p->label }}" style="width:100%;height:100%;object-fit:cover">
                    @unless($p->active)<span class="badge bg-secondary" style="position:absolute;top:8px;left:8px">Oculta</span>@endunless
                </div>
                <form method="POST" action="{{ route('admin.showcase.update', $p) }}" class="p-2">
                    @csrf @method('PUT')
                    <input type="text" name="label" value="{{ $p->label }}" class="form-control form-control-sm mb-2" placeholder="Etiqueta">
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number" name="sort" value="{{ $p->sort }}" class="form-control form-control-sm" style="width:72px" title="Orden">
                        <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $p->active ? 'checked' : '' }} id="a{{ $p->id }}"><label class="form-check-label small" for="a{{ $p->id }}">Visible</label></div>
                        <button class="btn btn-sm btn-outline-light ms-auto" title="Guardar"><i class="bi bi-save"></i></button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.showcase.destroy', $p) }}" onsubmit="return confirm('¿Eliminar esta foto?')" class="px-2 pb-2">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm w-100" style="color:#e0736b;border:1px solid var(--g-line)"><i class="bi bi-trash"></i> Eliminar</button>
                </form>
            </div>
        @endforeach
    </div>
@endif

<script>
// Vista previa de las fotos elegidas antes de guardar
document.querySelectorAll('#showcaseForm input[type=file]').forEach(function(inp){
    var box=document.createElement('div');
    box.className='d-flex flex-wrap gap-2 mt-2';
    inp.insertAdjacentElement('afterend', box);
    inp.addEventListener('change', function(){
        box.innerHTML='';
        Array.prototype.slice.call(inp.files, 0, 24).forEach(function(f){
            if(!f.type || f.type.indexOf('image/')!==0) return;
            var img=document.createElement('img');
            img.src=URL.createObjectURL(f);
            img.style.cssText='width:120px;height:70px;object-fit:cover;border-radius:6px;border:1px solid var(--g-line)';
            img.onload=function(){ URL.revokeObjectURL(img.src); };
            box.appendChild(img);
        });
    });
});
</script>
@endsection

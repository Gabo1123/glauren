@extends('layouts.admin')
@section('title', 'Testimonios')

@section('content')
<div class="g-topbar">
    <div>
        <h3 class="mb-1">Testimonios</h3>
        <p class="small text-secondary mb-0">Reseñas que aparecen en el home (sección “Lo que dicen las promociones”).</p>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

{{-- Agregar --}}
<div class="card p-4 mb-4" style="max-width:820px">
    <div class="g-card__title" style="color:var(--g-gold)">Agregar testimonio</div>
    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-2"><label class="form-label small">Texto</label><textarea name="text" class="form-control" rows="2" required></textarea></div>
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label small">Nombre</label><input name="name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label small">Rol (ej. Estudiante de once)</label><input name="role" class="form-control"></div>
            <div class="col-md-2"><label class="form-label small">Estrellas</label><input type="number" name="rating" class="form-control" value="5" min="1" max="5"></div>
            <div class="col-md-2"><label class="form-label small">Orden</label><input type="number" name="sort" class="form-control" value="0"></div>
            <div class="col-md-8"><label class="form-label small">Foto (opcional)</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
            <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" checked id="na"><label class="form-check-label" for="na">Visible</label></div></div>
        </div>
        <button class="btn btn-primary mt-3"><i class="bi bi-plus-lg"></i> Agregar</button>
    </form>
</div>

{{-- Lista --}}
@forelse($testimonials as $t)
    <div class="card p-3 mb-2" style="max-width:820px">
        <form method="POST" action="{{ route('admin.testimonials.update', $t) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="d-flex gap-3">
                <div class="text-center" style="flex-shrink:0">
                    @if($t->photoUrl())<img src="{{ $t->photoUrl() }}" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--g-line)">@else<div style="width:56px;height:56px;border-radius:50%;background:var(--g-surface-2);display:grid;place-items:center;color:var(--g-muted)"><i class="bi bi-person"></i></div>@endif
                    <div class="small mt-1" style="color:var(--g-gold)">{{ str_repeat('★', $t->rating) }}</div>
                </div>
                <div class="flex-grow-1">
                    <textarea name="text" class="form-control form-control-sm mb-2" rows="2">{{ $t->text }}</textarea>
                    <div class="row g-2">
                        <div class="col-md-4"><input name="name" value="{{ $t->name }}" class="form-control form-control-sm" placeholder="Nombre"></div>
                        <div class="col-md-4"><input name="role" value="{{ $t->role }}" class="form-control form-control-sm" placeholder="Rol"></div>
                        <div class="col-md-2"><input type="number" name="rating" value="{{ $t->rating }}" min="1" max="5" class="form-control form-control-sm" title="Estrellas"></div>
                        <div class="col-md-2"><input type="number" name="sort" value="{{ $t->sort }}" class="form-control form-control-sm" title="Orden"></div>
                        <div class="col-md-6"><input type="file" name="photo" class="form-control form-control-sm" accept="image/*"></div>
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $t->active?'checked':'' }} id="a{{ $t->id }}"><label class="form-check-label small" for="a{{ $t->id }}">Visible</label></div>
                            <button class="btn btn-sm btn-primary ms-auto"><i class="bi bi-save"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('¿Eliminar testimonio?')" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button></form>
    </div>
@empty
    <p class="text-secondary">Aún no hay testimonios. Agrega el primero arriba.</p>
@endforelse
@endsection

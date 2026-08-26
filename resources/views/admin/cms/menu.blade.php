@extends('layouts.admin')
@section('title', 'Menú')

@section('content')
<h3 class="mb-4">Menú del sitio</h3>

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Orden</th><th>Nombre</th><th>Enlace</th><th>Activo</th><th></th></tr></thead>
                    <tbody>
                    @forelse($items as $it)
                        <tr>
                            <td>{{ $it->sort }}</td>
                            <td>{{ $it->label }}</td>
                            <td class="text-secondary"><code>{{ $it->url }}</code></td>
                            <td>{!! $it->active ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.menu.edit', $it) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.menu.destroy', $it) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este ítem?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-3">Sin ítems.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="mb-3">Agregar ítem</h5>
            <form method="POST" action="{{ route('admin.menu.store') }}">
                @csrf
                <div class="mb-2"><label class="form-label small">Nombre</label><input type="text" name="label" class="form-control" required></div>
                <div class="mb-2"><label class="form-label small">Enlace (ej: /noticias)</label><input type="text" name="url" class="form-control" value="/" required></div>
                <div class="mb-2"><label class="form-label small">Orden</label><input type="number" name="sort" class="form-control" value="0"></div>
                <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="active" value="1" checked id="a"><label class="form-check-label small" for="a">Activo</label></div>
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Agregar</button>
            </form>
        </div>
    </div>
</div>
@endsection

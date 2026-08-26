@extends('layouts.admin')
@section('title', 'Tipos de chaqueta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Tipos de chaqueta</h3>
    <a href="{{ route('admin.types.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo tipo</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th></th><th>Nombre</th><th>Slug</th><th>Precio base</th><th>Modelo 3D</th><th>Activo</th><th></th></tr></thead>
            <tbody>
            @foreach($types as $t)
                <tr>
                    <td style="font-size:26px">{{ $t->icon }}</td>
                    <td>{{ $t->name }}</td>
                    <td class="text-secondary"><code>{{ $t->slug }}</code></td>
                    <td>${{ number_format($t->base_price, 0, ',', '.') }}</td>
                    <td>
                        @if($t->hasModel())
                            <span class="badge bg-success">Cargado</span>
                        @else
                            <span class="badge bg-secondary">Falta</span>
                        @endif
                    </td>
                    <td>{!! $t->active ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.types.edit', $t) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i> Editar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

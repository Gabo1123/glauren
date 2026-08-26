@extends('layouts.admin')
@section('title', 'Colegios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Colegios</h3>
    <a href="{{ route('admin.schools.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo colegio</a>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Colegio</th><th>Ciudad</th><th>Contacto</th><th>Contratos</th><th></th></tr></thead>
            <tbody>
            @forelse($schools as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td class="text-secondary">{{ $s->city ?: '—' }}</td>
                    <td class="text-secondary small">{{ $s->contact_name }} {{ $s->contact_phone ? '· '.$s->contact_phone : '' }}</td>
                    <td>{{ $s->contracts_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.schools.edit', $s) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.schools.destroy', $s) }}" class="d-inline" onsubmit="return confirm('¿Eliminar colegio y sus contratos?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-4">Aún no hay colegios. Crea el primero.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

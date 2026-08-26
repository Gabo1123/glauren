@extends('layouts.admin')
@section('title', 'Páginas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Páginas</h3>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva página</a>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Título</th><th>URL</th><th>Publicada</th><th></th></tr></thead>
            <tbody>
            @forelse($pages as $p)
                <tr>
                    <td>{{ $p->title }}</td>
                    <td class="text-secondary"><a href="{{ route('page.show', $p->slug) }}" target="_blank"><code>/p/{{ $p->slug }}</code></a></td>
                    <td>{!! $p->published ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $p) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary py-3">Aún no hay páginas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Noticias')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Noticias</h3>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva noticia</a>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th></th><th>Título</th><th>Publicada</th><th>Activa</th><th></th></tr></thead>
            <tbody>
            @forelse($posts as $p)
                <tr>
                    <td style="width:60px">@if($p->coverUrl())<img src="{{ $p->coverUrl() }}" style="width:48px;height:36px;object-fit:cover;border-radius:6px">@endif</td>
                    <td>{{ $p->title }}<div class="small text-secondary">/noticias/{{ $p->slug }}</div></td>
                    <td class="text-secondary small">{{ optional($p->published_at)->format('d/m/Y') }}</td>
                    <td>{!! $p->active ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.posts.edit', $p) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.posts.destroy', $p) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-3">Aún no hay noticias.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

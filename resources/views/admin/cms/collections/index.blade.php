@extends('layouts.admin')
@section('title', 'Colecciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Colecciones</h3>
    <a href="{{ route('admin.collections.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva colección</a>
</div>

@if($collections->isEmpty())
    <div class="card p-4 text-secondary">Aún no hay colecciones. Crea la primera (por ejemplo: Beisbolera Paño, Hoodies, Chaqueta Estructurada…).</div>
@else
    <div class="row g-3">
        @foreach($collections as $c)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card overflow-hidden">
                    <div style="aspect-ratio:4/5;background:#0c0e12 center/cover" @if($c->imageUrl()) style="aspect-ratio:4/5;background:#0c0e12 url('{{ $c->imageUrl() }}') center/cover" @endif></div>
                    <div class="p-2">
                        <div class="fw-semibold text-truncate">{{ $c->title }}</div>
                        <div class="small text-secondary d-flex align-items-center justify-content-between mt-1">
                            <span>Orden {{ $c->sort }} · {{ $c->active ? 'Activa' : 'Oculta' }}</span>
                            <span>
                                <a href="{{ route('admin.collections.edit', $c) }}" class="btn btn-sm btn-outline-light py-0"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.collections.destroy', $c) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

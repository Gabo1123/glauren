@extends('layouts.admin')
@section('title', 'Módulos · '.$page->title)

@section('content')
@php use App\Modules\Chaquetas\Models\HomeBlock; @endphp

<a href="{{ route('admin.pages.edit', $page) }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a la página</a>

<div class="d-flex justify-content-between align-items-center mt-2 mb-2 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Módulos de: {{ $page->title }}</h3>
        <div class="text-secondary small">Agrega módulos pre-diseñados, <b>arrastra ⠿ para ordenar</b> u ocúltalos. Aparecen debajo del contenido de la página.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-up-right"></i> Ver página</a>
        <a href="{{ route('admin.blocks.create', ['page' => $page->id]) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo módulo</a>
    </div>
</div>

@php $icons = ['texto'=>'bi-fonts','imagen_texto'=>'bi-image','cta'=>'bi-hand-index-thumb','video'=>'bi-play-btn','features'=>'bi-grid-3x3-gap','steps'=>'bi-list-ol','stats'=>'bi-123','html'=>'bi-code-slash']; @endphp

<div class="card p-3 mt-2">
    <div class="blk-list" data-position="page">
        @forelse($blocks as $b)
            <div class="blk-row" data-id="{{ $b->id }}">
                <span class="blk-drag" title="Arrastra para reordenar"><i class="bi bi-grip-vertical"></i></span>
                <i class="bi {{ $icons[$b->type] ?? 'bi-square' }} blk-type-ic"></i>
                <div class="blk-main">
                    <div class="blk-title">{{ $b->title ?: ('('.HomeBlock::TYPES[$b->type].')') }}</div>
                    <div class="blk-sub">{{ HomeBlock::TYPES[$b->type] ?? $b->type }}@if($b->subtitle) · {{ \Illuminate\Support\Str::limit($b->subtitle, 60) }}@endif</div>
                </div>
                @unless($b->active)<span class="badge bg-warning">Oculto</span>@endunless
                <div class="blk-actions">
                    <form method="POST" action="{{ route('admin.blocks.toggle', $b) }}" class="d-inline">@csrf
                        <button class="btn btn-sm btn-outline-light" title="{{ $b->active ? 'Ocultar' : 'Mostrar' }}"><i class="bi {{ $b->active ? 'bi-eye' : 'bi-eye-slash' }}"></i></button>
                    </form>
                    <a href="{{ route('admin.blocks.edit', $b) }}" class="btn btn-sm btn-outline-light" title="Editar"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.blocks.destroy', $b) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este módulo?')">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-secondary small py-3 text-center">Esta página aún no tiene módulos. Pulsa <b>«Nuevo módulo»</b> para agregar uno pre-diseñado.</div>
        @endforelse
    </div>
</div>

<style>
    .blk-list{ display:flex; flex-direction:column; gap:8px; min-height:8px; }
    .blk-row{ display:flex; align-items:center; gap:12px; padding:10px 12px; background:var(--g-surface-2); border:1px solid var(--g-line); border-radius:10px; }
    .blk-row.dragging{ opacity:.5; }
    .blk-drag{ cursor:grab; color:var(--g-muted); }
    .blk-type-ic{ color:var(--g-gold); font-size:18px; }
    .blk-main{ flex:1; min-width:0; }
    .blk-title{ font-weight:600; }
    .blk-sub{ font-size:12px; color:var(--g-muted); }
    .blk-actions{ display:flex; gap:6px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const url = @json(route('admin.blocks.reorder'));
    const csrf = @json(csrf_token());
    const list = document.querySelector('.blk-list');
    if(!list) return;
    new Sortable(list, { handle:'.blk-drag', animation:150, ghostClass:'dragging',
        onEnd: () => {
            const ids = [...list.querySelectorAll('.blk-row')].map(r => r.dataset.id);
            fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: JSON.stringify({ ids }) });
        }
    });
})();
</script>
@endsection

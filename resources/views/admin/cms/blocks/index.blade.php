@extends('layouts.admin')
@section('title', 'Bloques del home')

@section('content')
@php use App\Modules\Chaquetas\Models\HomeBlock; @endphp

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Bloques del home</h3>
        <div class="text-secondary small">Agrega, edita, <b>arrastra para reordenar</b> u oculta secciones. Se muestran en el home según su posición.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-up-right"></i> Ver home</a>
        <a href="{{ route('admin.blocks.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo bloque</a>
    </div>
</div>

@php $icons = ['texto'=>'bi-fonts','imagen_texto'=>'bi-image','cta'=>'bi-hand-index-thumb','video'=>'bi-play-btn','html'=>'bi-code-slash']; @endphp

@foreach(HomeBlock::POSITIONS as $posKey => $posLabel)
    <div class="card p-3 mt-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-secondary">{{ $posLabel }}</span>
            <span class="text-secondary small">{{ optional($blocks->get($posKey))->count() ?? 0 }} bloque(s)</span>
        </div>

        <div class="blk-list" data-position="{{ $posKey }}">
            @forelse($blocks->get($posKey) ?? [] as $b)
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
                        <form method="POST" action="{{ route('admin.blocks.destroy', $b) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este bloque?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="blk-empty text-secondary small">Sin bloques aquí. Crea uno y elige esta posición.</div>
            @endforelse
        </div>
    </div>
@endforeach

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
    .blk-empty{ padding:8px 4px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const url = @json(route('admin.blocks.reorder'));
    const csrf = @json(csrf_token());
    document.querySelectorAll('.blk-list').forEach(list => {
        new Sortable(list, {
            handle: '.blk-drag', animation: 150, ghostClass: 'dragging',
            onEnd: () => {
                const ids = [...list.querySelectorAll('.blk-row')].map(r => r.dataset.id);
                fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: JSON.stringify({ ids }) });
            }
        });
    });
})();
</script>
@endsection

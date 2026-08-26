@extends('layouts.admin')
@section('title', $block->exists ? 'Editar bloque' : 'Nuevo bloque')

@section('content')
@php use App\Modules\Chaquetas\Models\HomeBlock; $isNew = ! $block->exists; @endphp

<a href="{{ route('admin.blocks.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a bloques</a>
<h3 class="my-3">{{ $isNew ? 'Nuevo bloque' : 'Editar bloque' }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:820px">
    <form method="POST" action="{{ $isNew ? route('admin.blocks.store') : route('admin.blocks.update', $block) }}" enctype="multipart/form-data" id="blkForm">
        @csrf @unless($isNew) @method('PUT') @endunless
        <input type="hidden" name="page_id" value="{{ $block->page_id }}">
        @if($block->page_id)
            <input type="hidden" name="position" value="{{ $block->position ?: 'before_gallery' }}">
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small">Tipo de bloque</label>
                <select name="type" id="blkType" class="form-select">
                    @foreach(HomeBlock::TYPES as $k => $label)
                        <option value="{{ $k }}" {{ old('type', $block->type)===$k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @unless($block->page_id)
            <div class="col-md-6">
                <label class="form-label small">Posición en el home</label>
                <select name="position" class="form-select">
                    @foreach(HomeBlock::POSITIONS as $k => $label)
                        <option value="{{ $k }}" {{ old('position', $block->position)===$k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endunless

            <div class="col-12" data-fields="texto,imagen_texto,cta,features,steps,stats,galeria">
                <label class="form-label small">Título</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $block->title) }}">
            </div>

            <div class="col-12" data-fields="texto,imagen_texto,cta,features,steps,stats,galeria">
                <label class="form-label small">Subtítulo / bajada</label>
                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $block->subtitle) }}">
            </div>

            <div class="col-12" data-fields="galeria">
                <label class="form-label small">Fotos de la galería (puedes seleccionar varias)</label>
                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                @php $gal = ($block->type === 'galeria' && is_array($block->data)) ? $block->data : []; @endphp
                @if(count($gal))
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($gal as $img)
                            <label style="position:relative;cursor:pointer">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" class="rounded" style="height:80px;width:80px;object-fit:cover;border:1px solid var(--g-line)">
                                <span style="position:absolute;top:2px;right:2px;background:#000;border-radius:4px;padding:1px 4px"><input type="checkbox" name="remove_gallery[]" value="{{ $img }}" title="Quitar"></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="small text-secondary mt-1">Marca la casilla ☑ de una foto para quitarla al guardar.</div>
                @endif
            </div>

            @php
                $isStats = old('type', $block->type) === 'stats';
                $itemsPrefill = (! $isStats && $block->data) ? $block->data : [['emoji'=>'','title'=>'','desc'=>'']];
                $statsPrefill = ($isStats && $block->data) ? $block->data : [['value'=>'','label'=>'']];
            @endphp
            <div class="col-12" data-fields="features,steps">
                <label class="form-label small">Ítems <span class="text-secondary">(cada tarjeta: emoji · título · descripción)</span></label>
                <div id="repItems">
                    @foreach($itemsPrefill as $i => $it)
                    <div class="rep-row d-flex gap-2 mb-2 align-items-center">
                        <span class="rep-drag" title="Arrastra para mover"><i class="bi bi-grip-vertical"></i></span>
                        <input type="text" name="items[{{ $i }}][emoji]" class="form-control form-control-sm text-center" style="max-width:56px" value="{{ $it['emoji'] ?? '' }}" placeholder="🛡️">
                        <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm" style="max-width:200px" value="{{ $it['title'] ?? '' }}" placeholder="Título">
                        <input type="text" name="items[{{ $i }}][desc]" class="form-control form-control-sm" value="{{ $it['desc'] ?? '' }}" placeholder="Descripción">
                        <button type="button" class="btn btn-sm btn-outline-danger rep-del">&times;</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repItems"><i class="bi bi-plus-lg"></i> Agregar ítem</button>
            </div>
            <div class="col-12" data-fields="stats">
                <label class="form-label small">Números <span class="text-secondary">(valor · etiqueta)</span></label>
                <div id="repStats">
                    @foreach($statsPrefill as $i => $st)
                    <div class="rep-row d-flex gap-2 mb-2 align-items-center">
                        <span class="rep-drag" title="Arrastra para mover"><i class="bi bi-grip-vertical"></i></span>
                        <input type="text" name="stats[{{ $i }}][value]" class="form-control form-control-sm" style="max-width:120px" value="{{ $st['value'] ?? '' }}" placeholder="500+">
                        <input type="text" name="stats[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $st['label'] ?? '' }}" placeholder="Promociones felices">
                        <button type="button" class="btn btn-sm btn-outline-danger rep-del">&times;</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repStats"><i class="bi bi-plus-lg"></i> Agregar número</button>
            </div>

            <div class="col-12" data-fields="texto,imagen_texto,html">
                <label class="form-label small blk-body-label">Contenido (permite HTML)</label>
                <textarea name="body" class="form-control" rows="6" style="font-family:monospace;font-size:13px">{{ old('body', $block->body) }}</textarea>
            </div>

            <div class="col-12" data-fields="imagen_texto">
                <label class="form-label small">Imagen</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                @if($block->imageUrl())<img src="{{ $block->imageUrl() }}" class="mt-2 rounded" style="max-height:90px">@endif
            </div>

            <div class="col-md-6" data-fields="cta,imagen_texto">
                <label class="form-label small">Texto del botón</label>
                <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $block->button_text) }}" placeholder="ej. Pedir cotización">
            </div>
            <div class="col-md-6" data-fields="cta,imagen_texto">
                <label class="form-label small">Enlace del botón</label>
                <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $block->button_url) }}" placeholder="/contacto o https://wa.me/…">
            </div>

            <div class="col-12" data-fields="video">
                <label class="form-label small">Link del video (YouTube o TikTok)</label>
                <input type="text" name="video_url" class="form-control" value="{{ old('video_url', $block->video_url) }}" placeholder="https://youtu.be/… o https://www.tiktok.com/@…/video/…">
            </div>

            <div class="col-md-4" data-fields="imagen_texto">
                <label class="form-label small">Posición de la imagen</label>
                <select name="img_pos" class="form-select">
                    @php $ip = old('img_pos', $block->img_pos ?: 'left'); @endphp
                    <option value="left" {{ $ip==='left'?'selected':'' }}>Izquierda</option>
                    <option value="right" {{ $ip==='right'?'selected':'' }}>Derecha</option>
                </select>
            </div>

            <div class="col-md-4" data-fields="features,steps,stats,galeria">
                <label class="form-label small">Columnas</label>
                <select name="columns" class="form-select">
                    <option value="">Automático</option>
                    @for($n = 1; $n <= 6; $n++)
                        <option value="{{ $n }}" {{ (string) old('columns', $block->columns) === (string) $n ? 'selected' : '' }}>{{ $n }} columna{{ $n > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-4" data-fields="texto,imagen_texto,cta,video,features,steps,stats,html,galeria">
                <label class="form-label small">Fondo (color del texto)</label>
                <select name="theme" class="form-select">
                    <option value="dark" {{ old('theme', $block->theme)==='dark'?'selected':'' }}>Oscuro (texto claro)</option>
                    <option value="light" {{ old('theme', $block->theme)==='light'?'selected':'' }}>Claro (texto oscuro)</option>
                    <option value="gold" {{ old('theme', $block->theme)==='gold'?'selected':'' }}>Dorado</option>
                </select>
            </div>
            <div class="col-md-4" data-fields="texto,imagen_texto,cta,video,features,steps,stats,html,galeria">
                <label class="form-label small">Color de fondo personalizado</label>
                <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="bgColorPick" value="{{ old('bg_color', $block->bg_color) ?: '#1a1a20' }}">
                    <input type="text" name="bg_color" id="bgColorText" class="form-control" value="{{ old('bg_color', $block->bg_color) }}" placeholder="Opcional (ej. #1a1a20)">
                    <button type="button" class="btn btn-outline-light" id="bgColorClear" title="Quitar">×</button>
                </div>
                <div class="small text-secondary mt-1">Vacío = usa el fondo de arriba. Con color = lo sobreescribe.</div>
            </div>
            <div class="col-12" data-fields="texto,imagen_texto,cta,video,features,steps,stats,html,galeria">
                <label class="form-label small">Imagen de fondo del bloque (opcional)</label>
                <input type="file" name="bg_image" class="form-control" accept="image/*">
                @if($block->bgImageUrl())
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <img src="{{ $block->bgImageUrl() }}" class="rounded" style="max-height:70px;border:1px solid var(--g-line)">
                        <label class="small text-secondary"><input type="checkbox" name="remove_bg_image" value="1"> Quitar imagen de fondo</label>
                    </div>
                @endif
                <div class="small text-secondary mt-1">Se le pone una capa oscura automática para que el texto se lea. Ideal horizontal (1600×900).</div>
            </div>
            <div class="col-md-4" data-fields="texto,imagen_texto,cta,video,features,steps,stats,html,galeria">
                <label class="form-label small">Ancho del contenido</label>
                <select name="width" class="form-select">
                    @php $w = old('width', $block->width ?: 'normal'); @endphp
                    <option value="normal" {{ $w==='normal'?'selected':'' }}>Normal</option>
                    <option value="wide" {{ $w==='wide'?'selected':'' }}>Ancho</option>
                    <option value="full" {{ $w==='full'?'selected':'' }}>Completo</option>
                </select>
            </div>
            <div class="col-md-4" data-fields="texto,cta,video">
                <label class="form-label small">Alineación</label>
                <select name="align" class="form-select">
                    <option value="center" {{ old('align', $block->align)==='center'?'selected':'' }}>Centrado</option>
                    <option value="left" {{ old('align', $block->align)==='left'?'selected':'' }}>Izquierda</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Espaciado (aire arriba/abajo)</label>
                <select name="spacing" class="form-select">
                    @php $sp = old('spacing', $block->spacing ?: 'normal'); @endphp
                    <option value="compact" {{ $sp==='compact'?'selected':'' }}>Compacto</option>
                    <option value="normal" {{ $sp==='normal'?'selected':'' }}>Normal</option>
                    <option value="large" {{ $sp==='large'?'selected':'' }}>Amplio</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="blkActive" {{ old('active', $block->active) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="blkActive">Visible</label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            <a href="{{ route('admin.blocks.index') }}" class="btn btn-outline-light">Cancelar</a>
        </div>
    </form>
</div>

<style>.rep-drag{ cursor:grab; color:var(--g-muted); display:flex; align-items:center; font-size:16px; }</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const sel = document.getElementById('blkType');
    function apply(){
        const t = sel.value;
        document.querySelectorAll('[data-fields]').forEach(el => {
            const types = el.dataset.fields.split(',');
            el.style.display = types.includes(t) ? '' : 'none';
        });
        // Etiqueta del contenido según tipo
        const lbl = document.querySelector('.blk-body-label');
        if (lbl) lbl.textContent = t === 'html' ? 'HTML libre (se inserta tal cual)' : 'Contenido (permite HTML)';
    }
    sel.addEventListener('change', apply); apply();

    // Repetidores de ítems / números
    var c = 5000;
    function bindDel(row){
        var d = row.querySelector('.rep-del');
        if(d) d.onclick = function(){
            var rep = row.parentNode;
            if(rep.querySelectorAll('.rep-row').length > 1) row.remove();
            else row.querySelectorAll('input').forEach(function(i){ i.value=''; });
        };
    }
    document.querySelectorAll('.rep-row').forEach(bindDel);
    document.querySelectorAll('.rep-add').forEach(function(btn){
        btn.addEventListener('click', function(){
            var rep = document.getElementById(btn.dataset.rep);
            var rows = rep.querySelectorAll('.rep-row');
            var clone = rows[rows.length-1].cloneNode(true);
            var idx = ++c;
            clone.querySelectorAll('input').forEach(function(inp){ inp.value=''; inp.name = inp.name.replace(/\[\d+\]/, '['+idx+']'); });
            rep.appendChild(clone); bindDel(clone);
            var f = clone.querySelector('input'); if(f) f.focus();
        });
    });

    // Color de fondo personalizado (sincroniza picker <-> texto)
    var pick = document.getElementById('bgColorPick'), txt = document.getElementById('bgColorText'), clr = document.getElementById('bgColorClear');
    if(pick && txt){
        pick.addEventListener('input', function(){ txt.value = pick.value; });
        txt.addEventListener('input', function(){ if(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(txt.value)) pick.value = txt.value; });
        clr.addEventListener('click', function(){ txt.value = ''; });
    }

    // Arrastrar para mover elementos dentro del bloque
    if(window.Sortable){
        ['repItems','repStats'].forEach(function(id){
            var el = document.getElementById(id);
            if(el) new Sortable(el, { handle:'.rep-drag', animation:150 });
        });
    }
})();
</script>
@endsection

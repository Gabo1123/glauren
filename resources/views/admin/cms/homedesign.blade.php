@extends('layouts.admin')
@section('title', 'Diseño del Home')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Diseño del Home</h3>
        <div class="text-secondary small">Arrastra ⠿ para <b>reordenar</b> las secciones y usa el interruptor para <b>mostrar/ocultar</b>. Pulsa <b>Guardar orden</b>.</div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span id="hdStatus" class="small text-secondary"></span>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-up-right"></i> Ver home</a>
        <button class="btn btn-primary btn-sm" id="hdSave"><i class="bi bi-save"></i> Guardar orden</button>
    </div>
</div>

<div class="card p-3">
    <div id="hdList">
        @foreach($order as $o)
            @php $m = $meta[$o['key']]; @endphp
            <div class="hd-row" data-key="{{ $o['key'] }}">
                <span class="hd-drag" title="Arrastra para reordenar"><i class="bi bi-grip-vertical"></i></span>
                <span class="hd-em">{{ $m[1] }}</span>
                <div class="hd-main">
                    <div class="hd-title">{{ $m[0] }}</div>
                    <div class="hd-edit">Editar contenido en: <b>{{ $m[2] }}</b></div>
                </div>
                <div class="form-check form-switch hd-sw">
                    <input class="form-check-input hd-on" type="checkbox" {{ ($o['on'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label small">Visible</label>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="text-secondary small mt-2"><i class="bi bi-info-circle"></i> El <b>hero</b> (banner principal) y el <b>pie de página</b> quedan siempre fijos. Los <b>bloques nuevos</b> se manejan en «Bloques del home».</div>

<style>
    #hdList{ display:flex; flex-direction:column; gap:8px; }
    .hd-row{ display:flex; align-items:center; gap:14px; padding:12px 14px; background:var(--g-surface-2); border:1px solid var(--g-line); border-radius:10px; }
    .hd-row.dragging{ opacity:.5; }
    .hd-drag{ cursor:grab; color:var(--g-muted); font-size:18px; }
    .hd-em{ font-size:22px; }
    .hd-main{ flex:1; min-width:0; }
    .hd-title{ font-weight:600; }
    .hd-edit{ font-size:12px; color:var(--g-muted); }
    .hd-sw{ flex-shrink:0; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    var list = document.getElementById('hdList');
    var status = document.getElementById('hdStatus');
    new Sortable(list, { handle:'.hd-drag', animation:150, ghostClass:'dragging',
        onEnd:function(){ status.textContent = 'Cambios sin guardar'; status.style.color = 'var(--g-warn)'; } });
    list.querySelectorAll('.hd-on').forEach(function(c){ c.addEventListener('change', function(){ status.textContent='Cambios sin guardar'; status.style.color='var(--g-warn)'; }); });

    document.getElementById('hdSave').addEventListener('click', function(){
        var layout = [];
        list.querySelectorAll('.hd-row').forEach(function(row){
            layout.push({ key: row.dataset.key, on: row.querySelector('.hd-on').checked });
        });
        var btn = this; btn.disabled = true;
        fetch(@json(route('admin.homedesign.save')), {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},
            body: JSON.stringify({ layout: layout })
        }).then(function(r){ return r.json(); }).then(function(d){
            btn.disabled = false;
            if(d.ok){ status.textContent = '✓ Guardado'; status.style.color = 'var(--g-ok)'; }
            else { status.textContent = 'Error al guardar'; status.style.color = '#ff6b5e'; }
        }).catch(function(){ btn.disabled = false; status.textContent = 'Error de conexión'; status.style.color = '#ff6b5e'; });
    });
})();
</script>
@endsection

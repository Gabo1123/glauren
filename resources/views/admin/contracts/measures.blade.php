@extends('layouts.admin')
@section('title', 'Medidas · '.$contract->code)

@section('content')
<style>
    .mz-head{ display:flex; align-items:flex-end; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:18px; }
    .mz-head h3{ margin:0; }
    .mz-sub{ color:var(--g-muted); font-size:13px; margin-top:4px; }
    .mz-actions{ display:flex; gap:10px; align-items:center; }
    /* Barra de guardado pegada arriba */
    .mz-bar{ position:sticky; top:0; z-index:30; background:var(--g-ink); padding:12px 0 14px; margin-bottom:0;
        display:flex; align-items:center; justify-content:space-between; gap:14px; border-bottom:1px solid var(--g-line); }
    .mz-saved{ font-size:13px; color:var(--g-muted); }
    .mz-saved b{ color:var(--g-gold); }
    /* Hoja tipo Excel */
    .mz-sheet{ overflow:auto; max-height:calc(100vh - 210px); border:1px solid var(--g-line); border-radius:var(--g-r); background:var(--g-surface); }
    table.mz{ border-collapse:separate; border-spacing:0; width:100%; font-size:13px; min-width:1840px; }
    table.mz th, table.mz td{ border-right:1px solid var(--g-line); border-bottom:1px solid var(--g-line); padding:0; }
    table.mz thead th{ position:sticky; top:0; z-index:12; background:var(--g-surface-2); color:var(--g-muted);
        font-family:var(--g-font-label); text-transform:uppercase; letter-spacing:.06em; font-size:10.5px;
        padding:9px 10px; text-align:center; white-space:nowrap; }
    table.mz thead th .u{ display:block; font-size:9px; color:var(--g-muted); opacity:.7; letter-spacing:.04em; }
    /* Columna # */
    table.mz .col-n{ width:38px; text-align:center; color:var(--g-muted); font-family:var(--g-font-label); font-size:11px; background:var(--g-surface-2); }
    /* Columna nombre pegada a la izquierda */
    table.mz .col-name{ position:sticky; left:0; z-index:11; background:var(--g-surface); min-width:210px; }
    table.mz thead .col-name{ z-index:14; background:var(--g-surface-2); text-align:left; padding-left:14px; }
    table.mz tbody tr:hover td{ background:var(--g-surface-2); }
    table.mz tbody tr:hover .col-name{ background:var(--g-surface-2); }
    table.mz input, table.mz select{ width:100%; height:38px; background:transparent; border:0; color:var(--g-bone);
        padding:0 10px; font-size:13px; outline:none; }
    table.mz td.cell-name input{ font-weight:600; }
    table.mz .num input{ text-align:center; font-family:var(--g-font-mono, monospace); }
    table.mz td:focus-within{ box-shadow:inset 0 0 0 2px var(--g-gold); background:rgba(201,165,75,.06); }
    table.mz select option{ background:var(--g-surface); color:var(--g-bone); }
    table.mz input::placeholder{ color:var(--g-muted); opacity:.45; }
    .mz-doc{ width:120px; }
    /* Columna Pagado */
    table.mz .col-pay{ width:84px; }
    table.mz td.cell-pay{ text-align:center; }
    .mz-pay{ position:relative; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; height:38px; margin:0; padding:0 6px; }
    .mz-pay input{ position:absolute; top:0; left:0; opacity:0; width:100%; height:100%; margin:0; cursor:pointer; }
    .mz-pay span{ width:44px; height:24px; border-radius:999px; background:var(--g-surface-3); border:1px solid var(--g-line);
        position:relative; transition:var(--g-t); display:inline-block; }
    .mz-pay span::after{ content:""; position:absolute; top:2px; left:2px; width:18px; height:18px; border-radius:50%;
        background:var(--g-muted); transition:var(--g-t); }
    .mz-pay input:checked + span{ background:rgba(37,211,102,.22); border-color:var(--g-ok); }
    .mz-pay input:checked + span::after{ left:22px; background:var(--g-ok); }
    .mz-pay input:focus-visible + span{ box-shadow:0 0 0 3px rgba(201,165,75,.3); }
    /* Columnas por prenda (buzo/polo) */
    table.mz .gar-buzo{ border-left:2px solid rgba(90,160,255,.45); }
    table.mz .gar-polo{ border-left:2px solid rgba(180,120,255,.45); }
    table.mz .gar-gorra{ border-left:2px solid rgba(90,200,150,.45); }
    table.mz thead th.gar-buzo{ color:#8fb8ff; }
    table.mz thead th.gar-polo{ color:#c6a5ff; }
    table.mz thead th.gar-gorra{ color:#8fe0b6; }
    table.mz td.gar-off{ background:repeating-linear-gradient(45deg,transparent,transparent 6px,rgba(255,255,255,.03) 6px,rgba(255,255,255,.03) 12px); }
    table.mz td.gar-off input{ opacity:.22; pointer-events:none; }
    .mz-empty{ text-align:center; color:var(--g-muted); padding:46px 20px; }
    .mz-legend{ font-size:12px; color:var(--g-muted); margin-top:12px; display:flex; gap:18px; flex-wrap:wrap; }
    .mz-legend kbd{ background:var(--g-surface-2); border:1px solid var(--g-line); border-radius:5px; padding:1px 7px; font-size:11px; color:var(--g-bone); }
</style>

<a href="{{ route('admin.contracts.show', $contract) }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al contrato</a>

<div class="mz-head mt-2">
    <div>
        <h3>Toma de medidas</h3>
        <div class="mz-sub">{{ $contract->school->name ?? 'Colegio' }} · {{ $contract->code }} · {{ $students->count() }} alumno(s){{ $contract->jacketType ? ' · '.$contract->jacketType->name : '' }}</div>
    </div>
    <div class="mz-actions">
        <a href="{{ route('admin.students.export', $contract) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-file-earmark-excel"></i> Exportar</a>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($students->isEmpty())
    <div class="card"><div class="mz-empty">
        <i class="bi bi-people" style="font-size:32px;color:var(--g-muted)"></i>
        <p class="mt-2 mb-3">Todavía no hay alumnos en este contrato.<br>Agrégalos o impórtalos desde el contrato para tomarles las medidas aquí.</p>
        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-primary btn-sm">Ir a agregar alumnos</a>
    </div></div>
@else
<form method="POST" action="{{ route('admin.students.measures.save', $contract) }}" id="mzForm">
    @csrf @method('PUT')

    <div class="mz-bar">
        <div class="mz-saved" id="mzStatus">Edita las celdas como en Excel. Los cambios se guardan al presionar <b>Guardar medidas</b>.</div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-light btn-sm" type="button" id="mzAdd"><i class="bi bi-plus-lg"></i> Agregar alumno</button>
            <button class="btn btn-outline-light btn-sm" type="button" id="mzAdd5" title="Agregar 5 filas">+5</button>
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Guardar medidas</button>
        </div>
    </div>

    <div class="mz-sheet mt-3">
        <table class="mz">
            <thead>
                <tr>
                    <th class="col-n">#</th>
                    <th class="col-name">Alumno</th>
                    <th>Documento</th>
                    <th>Sexo</th>
                    <th>Apodo</th>
                    <th>Talla</th>
                    <th>Combo</th>
                    <th>Pecho <span class="u">cm</span></th>
                    <th>Cintura <span class="u">cm</span></th>
                    <th>Cadera <span class="u">cm</span></th>
                    <th>Manga <span class="u">cm</span></th>
                    <th>Talle <span class="u">cm</span></th>
                    <th class="gar-buzo">👕 Buzo pecho <span class="u">cm</span></th>
                    <th class="gar-buzo">Buzo cintura <span class="u">cm</span></th>
                    <th class="gar-buzo">Buzo largo <span class="u">cm</span></th>
                    <th class="gar-buzo">Buzo manga <span class="u">cm</span></th>
                    <th class="gar-polo">🎽 Polo pecho <span class="u">cm</span></th>
                    <th class="gar-polo">Polo largo <span class="u">cm</span></th>
                    <th class="gar-polo">Polo manga <span class="u">cm</span></th>
                    <th class="gar-gorra">🧢 Gorra cabeza <span class="u">cm</span></th>
                    <th class="col-pay">Pagado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $s)
                <tr>
                    <td class="col-n">{{ $i + 1 }}</td>
                    <td class="col-name cell-name"><input type="text" name="students[{{ $s->id }}][name]" value="{{ $s->name }}" data-col="0" autocomplete="off"></td>
                    <td class="cell"><input type="text" class="mz-doc" name="students[{{ $s->id }}][document]" value="{{ $s->document }}" data-col="1" autocomplete="off"></td>
                    <td class="cell">
                        <select name="students[{{ $s->id }}][gender]" data-col="2">
                            <option value="">—</option>
                            <option value="mujer" {{ $s->gender=='mujer'?'selected':'' }}>Mujer</option>
                            <option value="hombre" {{ $s->gender=='hombre'?'selected':'' }}>Hombre</option>
                        </select>
                    </td>
                    <td class="cell"><input type="text" name="students[{{ $s->id }}][nickname]" value="{{ $s->nickname }}" data-col="3" autocomplete="off"></td>
                    <td class="cell"><input type="text" name="students[{{ $s->id }}][size]" value="{{ $s->size }}" data-col="4" placeholder="M" autocomplete="off"></td>
                    <td class="cell"><select name="students[{{ $s->id }}][combo]" style="min-width:150px">
                        @foreach(\App\Modules\Chaquetas\Models\Student::COMBOS as $ck => $cc)
                            <option value="{{ $ck }}" {{ ($s->combo ?: $contract->defaultCombo()) === $ck ? 'selected' : '' }}>{{ $cc['label'] }}</option>
                        @endforeach
                    </select></td>
                    <td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][chest]" value="{{ $s->chest }}" data-col="5" placeholder="—"></td>
                    <td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][waist]" value="{{ $s->waist }}" data-col="6" placeholder="—"></td>
                    <td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][hip]" value="{{ $s->hip }}" data-col="7" placeholder="—"></td>
                    <td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][sleeve_length]" value="{{ $s->sleeve_length }}" data-col="8" placeholder="—"></td>
                    <td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][talle_length]" value="{{ $s->talle_length }}" data-col="9" placeholder="—"></td>
                    <td class="cell num gar-cell gar-buzo" data-gar="buzo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][buzo][chest]" value="{{ $s->measure('buzo','chest') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-buzo" data-gar="buzo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][buzo][waist]" value="{{ $s->measure('buzo','waist') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-buzo" data-gar="buzo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][buzo][length]" value="{{ $s->measure('buzo','length') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-buzo" data-gar="buzo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][buzo][sleeve]" value="{{ $s->measure('buzo','sleeve') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-polo" data-gar="polo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][polo][chest]" value="{{ $s->measure('polo','chest') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-polo" data-gar="polo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][polo][length]" value="{{ $s->measure('polo','length') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-polo" data-gar="polo"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][polo][sleeve]" value="{{ $s->measure('polo','sleeve') }}" placeholder="—"></td>
                    <td class="cell num gar-cell gar-gorra" data-gar="gorra"><input type="text" inputmode="decimal" name="students[{{ $s->id }}][measurements][gorra][head]" value="{{ $s->measure('gorra','head') }}" placeholder="—"></td>
                    <td class="cell-pay">
                        <input type="hidden" name="students[{{ $s->id }}][paid]" value="0">
                        <label class="mz-pay">
                            <input type="checkbox" name="students[{{ $s->id }}][paid]" value="1" {{ $s->isPaid() ? 'checked' : '' }}>
                            <span></span>
                        </label>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mz-legend">
        <span><kbd>Enter</kbd> o <kbd>↓</kbd> baja a la siguiente fila</span>
        <span><kbd>↑</kbd> sube · <kbd>Tab</kbd> avanza de celda</span>
        <span>Escribe las medidas en centímetros (ej. 96 o 96.5)</span>
    </div>
</form>

<script>
(function(){
    var form = document.getElementById('mzForm');
    var status = document.getElementById('mzStatus');
    var tbody = form.querySelector('table.mz tbody');
    var byCol = {};
    function rebuild(){
        byCol = {};
        Array.prototype.slice.call(form.querySelectorAll('[data-col]')).forEach(function(el){
            var c = el.getAttribute('data-col');
            (byCol[c] = byCol[c] || []).push(el);
        });
    }
    rebuild();

    // Navegación tipo Excel: Enter/flechas mueven verticalmente por la misma columna.
    form.addEventListener('keydown', function(e){
        var el = e.target;
        var col = el.getAttribute && el.getAttribute('data-col');
        if(col === null || col === undefined) return;
        var list = byCol[col]; if(!list) return;
        var idx = list.indexOf(el);
        if(idx < 0) return;

        if(e.key === 'Enter' || e.key === 'ArrowDown'){
            if(el.tagName === 'SELECT' && e.key === 'ArrowDown') return;
            e.preventDefault();
            if(idx === list.length-1){ addRow(false); rebuild(); } // en la última fila, crea una nueva
            var next = byCol[col][idx+1];
            if(next){ next.focus(); if(next.select) next.select(); }
        } else if(e.key === 'ArrowUp'){
            if(el.tagName === 'SELECT') return;
            e.preventDefault();
            var prev = list[idx-1];
            if(prev){ prev.focus(); if(prev.select) prev.select(); }
        }
    });

    // Aviso de cambios sin guardar.
    var dirty = false;
    function markDirty(){
        if(!dirty){ dirty = true;
            status.innerHTML = '<b style="color:var(--g-warn)">Cambios sin guardar.</b> Presiona <b>Guardar medidas</b> para no perderlos.'; }
    }
    form.addEventListener('input', markDirty);
    form.addEventListener('submit', function(){ dirty = false; });
    window.addEventListener('beforeunload', function(e){ if(dirty){ e.preventDefault(); e.returnValue = ''; } });

    // Agregar filas nuevas (se crean como alumnos al guardar)
    var newIdx = 0;
    var NUM = ['chest','waist','hip','sleeve_length','talle_length'];
    var GAR = [['buzo','chest'],['buzo','waist'],['buzo','length'],['buzo','sleeve'],['polo','chest'],['polo','length'],['polo','sleeve'],['gorra','head']];
    var COMBO_OPTS = @json(collect(\App\Modules\Chaquetas\Models\Student::COMBOS)->map(fn($c, $k) => '<option value="'.$k.'"'.($contract->defaultCombo() === $k ? ' selected' : '').'>'.$c['label'].'</option>')->implode(''));

    // Muestra/oculta las celdas de cada prenda según el combo de la fila.
    var COMBOS_MAP = @json(collect(\App\Modules\Chaquetas\Models\Student::COMBOS)->map(fn($c) => $c['garments']));
    function syncRow(tr){
        var sel = tr.querySelector('select[name*="[combo]"]');
        var gs = COMBOS_MAP[sel ? sel.value : 'chaqueta'] || ['chaqueta'];
        tr.querySelectorAll('.gar-cell').forEach(function(td){
            var on = gs.indexOf(td.getAttribute('data-gar')) !== -1;
            var inp = td.querySelector('input');
            if(inp) inp.disabled = !on;
            td.classList.toggle('gar-off', !on);
        });
    }
    function syncAll(){ Array.prototype.slice.call(tbody.querySelectorAll('tr')).forEach(syncRow); }
    form.addEventListener('change', function(e){
        if(e.target.matches && e.target.matches('select[name*="[combo]"]')){ syncRow(e.target.closest('tr')); markDirty(); }
    });
    syncAll();
    function addRow(focus){
        var i = newIdx++;
        var n = tbody.querySelectorAll('tr').length + 1;
        var tr = document.createElement('tr');
        tr.className = 'mz-new';
        var html = '<td class="col-n">'+n+'</td>'
            + '<td class="col-name cell-name"><input type="text" name="new['+i+'][name]" data-col="0" placeholder="Nombre nuevo" autocomplete="off"></td>'
            + '<td class="cell"><input type="text" class="mz-doc" name="new['+i+'][document]" data-col="1" placeholder="Documento" autocomplete="off"></td>'
            + '<td class="cell"><select name="new['+i+'][gender]" data-col="2"><option value="">—</option><option value="mujer">Mujer</option><option value="hombre">Hombre</option></select></td>'
            + '<td class="cell"><input type="text" name="new['+i+'][nickname]" data-col="3" autocomplete="off"></td>'
            + '<td class="cell"><input type="text" name="new['+i+'][size]" data-col="4" placeholder="M" autocomplete="off"></td>'
            + '<td class="cell"><select name="new['+i+'][combo]" style="min-width:150px">' + COMBO_OPTS + '</select></td>';
        NUM.forEach(function(f, k){
            html += '<td class="cell num gar-cell" data-gar="chaqueta"><input type="text" inputmode="decimal" name="new['+i+']['+f+']" data-col="'+(5+k)+'" placeholder="—"></td>';
        });
        GAR.forEach(function(g){
            html += '<td class="cell num gar-cell gar-'+g[0]+'" data-gar="'+g[0]+'"><input type="text" inputmode="decimal" name="new['+i+'][measurements]['+g[0]+']['+g[1]+']" placeholder="—"></td>';
        });
        html += '<td class="cell-pay"><input type="hidden" name="new['+i+'][paid]" value="0"><label class="mz-pay"><input type="checkbox" name="new['+i+'][paid]" value="1"><span></span></label></td>';
        tr.innerHTML = html;
        tbody.appendChild(tr);
        syncRow(tr);
        rebuild(); markDirty();
        if(focus){ var f2 = tr.querySelector('input'); if(f2) f2.focus(); }
    }
    document.getElementById('mzAdd').addEventListener('click', function(){ addRow(true); });
    document.getElementById('mzAdd5').addEventListener('click', function(){ for(var k=0;k<5;k++) addRow(false); markDirty(); });
})();
</script>
@endif
@endsection

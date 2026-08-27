@extends('layouts.admin')
@section('title', 'Medidas de '.$student->name)

@section('content')
<a href="{{ route('admin.contracts.show', $contract) }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al contrato</a>
<h3 class="my-3">{{ $student->name }} <span class="text-secondary" style="font-size:16px">· {{ $contract->code }}</span></h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:760px">
    <form method="POST" action="{{ route('admin.students.update', [$contract, $student]) }}">
        @csrf @method('PUT')
        <div class="g-card__title" style="color:var(--g-gold)">Datos del alumno</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6"><label class="form-label small">Tarjeta de identidad</label><input type="text" name="document" class="form-control" value="{{ old('document', $student->document) }}" required></div>
            <div class="col-md-6"><label class="form-label small">Nombre</label><input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" required></div>
            <div class="col-md-6"><label class="form-label small">Sexo</label><select name="gender" class="form-select"><option value="">—</option><option value="hombre" {{ $student->gender=='hombre'?'selected':'' }}>Hombre</option><option value="mujer" {{ $student->gender=='mujer'?'selected':'' }}>Mujer</option></select></div>
            <div class="col-md-6"><label class="form-label small">Apodo (para bordar)</label><input type="text" name="nickname" class="form-control" value="{{ old('nickname', $student->nickname) }}"></div>
        </div>

        {{-- Líneas de prendas: cada una con su talla, cantidad, valor y medidas --}}
        <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
            <div class="g-card__title mb-0" style="color:var(--g-gold)">Prendas del alumno</div>
            <div class="fw-semibold">Total: <span id="stuTotal" style="color:var(--g-gold)">$0</span></div>
        </div>
        <p class="small text-secondary mb-2">Agrega cada prenda con su talla, cantidad, valor y medidas. Un alumno puede llevar varias (ej: 1 chaqueta + 1 buzo, o 2 chaquetas de tallas distintas). El valor es interno y <b>no se le muestra al alumno</b>.</p>
        <div id="itemsBox"></div>
        <button type="button" id="addItem" class="btn btn-outline-light btn-sm mb-2"><i class="bi bi-plus-lg"></i> Agregar prenda</button>

        <div class="g-card__title mt-3" style="color:var(--g-gold)">Pago</div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Estado</label><select name="payment_status" class="form-select"><option value="pendiente" {{ $student->payment_status=='pendiente'?'selected':'' }}>Pendiente</option><option value="pagado" {{ $student->payment_status=='pagado'?'selected':'' }}>Pagado</option></select></div>
            <div class="col-md-6"><label class="form-label small">Valor total del alumno</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $student->amount) }}"><div class="small text-secondary mt-1">Se calcula solo desde las prendas de arriba; puedes ajustarlo si lo necesitas.</div></div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>

<script>
(function(){
    var GARMENTS = @json(collect(\App\Modules\Chaquetas\Models\Student::GARMENTS)->map(fn($g) => ['label'=>$g['label'],'icon'=>$g['icon'],'fields'=>$g['fields']]));
    var LINES = @json($student->lines());
    var box = document.getElementById('itemsBox');
    var totalEl = document.getElementById('stuTotal');
    var idx = 0;

    function esc(s){ return String(s==null?'':s).replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
    function fmtCOP(n){ return '$'+Math.round(n||0).toLocaleString('es-CO'); }

    function garmentOptions(sel){
        var h='';
        for(var k in GARMENTS){ h+='<option value="'+k+'"'+(k===sel?' selected':'')+'>'+GARMENTS[k].icon+' '+GARMENTS[k].label+'</option>'; }
        return h;
    }
    function measuresHtml(i, garment, m){
        m = m || {};
        var g = GARMENTS[garment] || GARMENTS['chaqueta'], h='';
        for(var f in g.fields){
            h+='<div class="col-6 col-md-3"><label class="form-label small text-secondary mb-1">'+g.fields[f]+' <span style="opacity:.6">cm</span></label>'
             +'<input type="number" step="0.1" name="items['+i+'][m]['+f+']" class="form-control form-control-sm" value="'+esc(m[f])+'"></div>';
        }
        return h;
    }
    function lineHtml(i, line){
        var g = line.garment || 'chaqueta';
        return '<div class="card p-3 mb-2 item-line" data-i="'+i+'" style="background:var(--g-surface-2)">'
          + '<div class="row g-2 align-items-end">'
          +   '<div class="col-6 col-md-3"><label class="form-label small mb-1">Prenda</label><select name="items['+i+'][garment]" class="form-select form-select-sm il-garment">'+garmentOptions(g)+'</select></div>'
          +   '<div class="col-6 col-md-2"><label class="form-label small mb-1">Talla</label><input name="items['+i+'][size]" class="form-control form-control-sm" value="'+esc(line.size)+'" placeholder="M"></div>'
          +   '<div class="col-6 col-md-2"><label class="form-label small mb-1">Cantidad</label><input type="number" min="1" name="items['+i+'][qty]" class="form-control form-control-sm il-qty" value="'+(parseInt(line.qty,10)||1)+'"></div>'
          +   '<div class="col-6 col-md-3"><label class="form-label small mb-1">Valor c/u</label><input type="number" min="0" step="1" name="items['+i+'][price]" class="form-control form-control-sm il-price" value="'+esc(line.price)+'" placeholder="0"></div>'
          +   '<div class="col-12 col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger il-del w-100"><i class="bi bi-trash"></i></button></div>'
          + '</div>'
          + '<div class="row g-2 mt-1 il-measures">'+measuresHtml(i, g, line.m)+'</div>'
          + '</div>';
    }
    var amountEl = document.querySelector('[name="amount"]');
    function recalc(){
        var t=0;
        box.querySelectorAll('.item-line').forEach(function(l){
            var q=parseFloat(l.querySelector('.il-qty').value)||0, p=parseFloat(l.querySelector('.il-price').value)||0;
            t+=q*p;
        });
        totalEl.textContent=fmtCOP(t);
        if(amountEl && t>0) amountEl.value=Math.round(t);
    }
    function addLine(line){
        var i=idx++;
        box.insertAdjacentHTML('beforeend', lineHtml(i, line||{garment:'chaqueta',qty:1}));
        recalc();
    }

    // Render inicial
    (LINES && LINES.length ? LINES : [{garment:'chaqueta',qty:1}]).forEach(function(l){ addLine(l); });

    document.getElementById('addItem').addEventListener('click', function(){ addLine({garment:'chaqueta',qty:1}); });
    box.addEventListener('input', recalc);
    box.addEventListener('change', function(e){
        if(e.target.classList.contains('il-garment')){
            var line=e.target.closest('.item-line'), i=line.getAttribute('data-i');
            line.querySelector('.il-measures').innerHTML=measuresHtml(i, e.target.value, {});
            recalc();
        }
    });
    box.addEventListener('click', function(e){
        var del=e.target.closest('.il-del');
        if(del){ var lines=box.querySelectorAll('.item-line'); if(lines.length>1){ del.closest('.item-line').remove(); recalc(); } }
    });
})();
</script>
@endsection

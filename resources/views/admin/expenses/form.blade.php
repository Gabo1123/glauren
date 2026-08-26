@extends('layouts.admin')
@section('title', $expense->exists ? 'Editar gasto' : 'Nuevo gasto')

@section('content')
@php $isNew = ! $expense->exists; @endphp
<a href="{{ route('admin.expenses.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Gastos</a>
<h3 class="my-3">{{ $isNew ? 'Nuevo gasto' : 'Editar gasto' }}</h3>

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card p-4" style="max-width:760px">
    <form method="POST" action="{{ $isNew ? route('admin.expenses.store') : route('admin.expenses.update', $expense) }}" enctype="multipart/form-data" id="expForm">
        @csrf @unless($isNew) @method('PUT') @endunless

        <div class="row g-3">
            <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" name="date" class="form-control" value="{{ old('date', optional($expense->date)->toDateString() ?? $expense->date) }}" required></div>
            <div class="col-md-4"><label class="form-label small">Categoría</label>
                <select name="category" class="form-select" required>
                    @foreach($categories as $k=>$v)<option value="{{ $k }}" {{ old('category',$expense->category)===$k?'selected':'' }}>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small">Valor (COP)</label><input type="number" step="1" min="0" name="amount" class="form-control" value="{{ old('amount', $expense->amount ? (int)$expense->amount : '') }}" required></div>

            <div class="col-md-8"><label class="form-label small">Concepto (qué compraste)</label><input type="text" name="concept" class="form-control" value="{{ old('concept', $expense->concept) }}" placeholder="Ej: Rollo de paño azul" required></div>
            <div class="col-md-4"><label class="form-label small">Proveedor</label><input type="text" name="supplier" class="form-control" value="{{ old('supplier', $expense->supplier) }}" placeholder="Ej: Lafayette"></div>

            <div class="col-12"><hr class="my-1" style="border-color:var(--g-line)"><div class="small text-secondary mb-1"><i class="bi bi-paperclip"></i> Archívalo (todo opcional)</div></div>
            <div class="col-md-4"><label class="form-label small">Colegio</label>
                <select name="school_id" class="form-select">
                    <option value="">—</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ (string)old('school_id',$expense->school_id)===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small">Contrato</label>
                <select name="contract_id" class="form-select">
                    <option value="">—</option>
                    @foreach($contracts as $c)<option value="{{ $c->id }}" {{ (string)old('contract_id',$expense->contract_id)===(string)$c->id?'selected':'' }}>{{ $c->code }} · {{ $c->school?->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small">Tipo de chaqueta</label>
                <select name="jacket_type_id" class="form-select">
                    <option value="">—</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ (string)old('jacket_type_id',$expense->jacket_type_id)===(string)$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label small">Material / tela (texto libre)</label><input type="text" name="material" class="form-control" value="{{ old('material', $expense->material) }}" placeholder="Ej: Tela cuerpo (paño)"></div>
            <div class="col-md-6"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control" value="{{ old('notes', $expense->notes) }}"></div>

            <div class="col-12"><hr class="my-1" style="border-color:var(--g-line)"></div>
            <div class="col-12">
                <label class="form-label small"><i class="bi bi-camera"></i> Factura (foto o PDF)</label>
                <input type="file" name="invoice" class="form-control" accept="image/*,application/pdf" capture="environment">
                <div class="small text-secondary mt-1">Puedes tomarle una foto con el celular. Máx 8MB.</div>
                @if($expense->invoiceUrl())
                    <div class="mt-2">
                        @if($expense->invoiceIsPdf())
                            <a href="{{ $expense->invoiceUrl() }}" target="_blank"><i class="bi bi-file-earmark-pdf" style="font-size:34px;color:#e0736b"></i> Ver factura actual (PDF)</a>
                        @else
                            <img src="{{ $expense->invoiceUrl() }}" style="max-height:140px;border-radius:8px;border:1px solid var(--g-line)">
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>

<script>
document.querySelectorAll('#expForm input[type=file]').forEach(function(inp){
    var box=document.createElement('div'); box.className='mt-2';
    inp.insertAdjacentElement('afterend', box);
    inp.addEventListener('change', function(){
        box.innerHTML='';
        var f=inp.files[0]; if(!f) return;
        if(f.type.indexOf('image/')===0){
            var img=document.createElement('img'); img.src=URL.createObjectURL(f);
            img.style.cssText='max-height:150px;border-radius:8px;border:1px solid var(--g-line)';
            img.onload=function(){URL.revokeObjectURL(img.src)}; box.appendChild(img);
        } else { box.textContent='📄 '+f.name; box.className='mt-2 text-secondary small'; }
    });
});
</script>
@endsection

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
            <div class="col-md-4"><label class="form-label small">Sexo</label><select name="gender" class="form-select"><option value="">—</option><option value="hombre" {{ $student->gender=='hombre'?'selected':'' }}>Hombre</option><option value="mujer" {{ $student->gender=='mujer'?'selected':'' }}>Mujer</option></select></div>
            <div class="col-md-4"><label class="form-label small">Apodo</label><input type="text" name="nickname" class="form-control" value="{{ old('nickname', $student->nickname) }}"></div>
            <div class="col-md-4"><label class="form-label small">Talla</label><input type="text" name="size" class="form-control" value="{{ old('size', $student->size) }}"></div>
        </div>

        {{-- Combo de prendas: al cambiarlo aparecen las medidas de cada prenda --}}
        <div class="g-card__title mt-3" style="color:var(--g-gold)">Prendas (combo)</div>
        <div class="row g-3 mb-1">
            <div class="col-md-8">
                <label class="form-label small">¿Qué va a llevar este alumno?</label>
                <select name="combo" id="comboSel" class="form-select">
                    @foreach(\App\Modules\Chaquetas\Models\Student::COMBOS as $key => $c)
                        <option value="{{ $key }}" {{ ($student->combo ?: 'chaqueta') === $key ? 'selected' : '' }}>{{ $c['label'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">Según el combo aparecen abajo las medidas de cada prenda.</div>
            </div>
        </div>

        @php $garments = \App\Modules\Chaquetas\Models\Student::GARMENTS; @endphp
        @foreach($garments as $gkey => $g)
            @php $base = ! empty($g['base']); @endphp
            <div class="garment-block" data-garment="{{ $gkey }}" style="display:none">
                <div class="g-card__title mt-3" style="color:var(--g-gold)">{{ $g['icon'] }} Medidas · {{ $g['label'] }} <span class="text-secondary" style="font-size:12px;font-weight:400">(cm)</span></div>
                <div class="row g-3 mb-2">
                    @foreach($g['fields'] as $field => $label)
                        @php
                            if ($base) {
                                $name = $field; $val = old($field, $student->$field);
                            } else {
                                $name = "measurements[$gkey][$field]"; $val = old("measurements.$gkey.$field", $student->measure($gkey, $field));
                            }
                        @endphp
                        <div class="col-md-4">
                            <label class="form-label small">{{ $label }}</label>
                            <input type="number" step="0.1" name="{{ $name }}" class="form-control" value="{{ $val }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="g-card__title mt-3" style="color:var(--g-gold)">Pago</div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Estado</label><select name="payment_status" class="form-select"><option value="pendiente" {{ $student->payment_status=='pendiente'?'selected':'' }}>Pendiente</option><option value="pagado" {{ $student->payment_status=='pagado'?'selected':'' }}>Pagado</option></select></div>
            <div class="col-md-6"><label class="form-label small">Valor</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $student->amount) }}"></div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>

<script>
(function(){
    var combos = @json(collect(\App\Modules\Chaquetas\Models\Student::COMBOS)->map(fn($c) => $c['garments']));
    var sel = document.getElementById('comboSel');
    var blocks = document.querySelectorAll('.garment-block');
    function refresh(){
        var active = combos[sel.value] || ['chaqueta'];
        blocks.forEach(function(b){
            b.style.display = active.indexOf(b.getAttribute('data-garment')) !== -1 ? '' : 'none';
        });
    }
    sel.addEventListener('change', refresh);
    refresh();
})();
</script>
@endsection

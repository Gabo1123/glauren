@extends('layouts.admin')
@section('title', $contract->exists ? 'Editar contrato' : 'Nuevo contrato')

@section('content')
@php $isNew = ! $contract->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nuevo contrato' : 'Editar contrato '.$contract->code }}</h3>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($schools->isEmpty())
    <div class="alert alert-warning">Primero crea un <a href="{{ route('admin.schools.create') }}">colegio</a>.</div>
@endif

<div class="card p-4" style="max-width:700px">
    <form method="POST" action="{{ $isNew ? route('admin.contracts.store') : route('admin.contracts.update', $contract) }}">
        @csrf @unless($isNew) @method('PUT') @endunless
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label small">Colegio</label>
                <select name="school_id" class="form-select" required>
                    <option value="">— Elegir —</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ old('school_id', $contract->school_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small">Año</label><input type="number" name="year" class="form-control" value="{{ old('year', $contract->year) }}"></div>

            <div class="col-md-6">
                <label class="form-label small">Tipo de chaqueta</label>
                <select name="jacket_type_id" class="form-select">
                    <option value="">— (opcional) —</option>
                    @foreach($types as $t)<option value="{{ $t->id }}" {{ old('jacket_type_id', $contract->jacket_type_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small">🧥 Combo de prendas del contrato</label>
                <select name="default_combo" class="form-select">
                    @foreach(\App\Modules\Chaquetas\Models\Student::COMBOS as $ck => $cc)
                        <option value="{{ $ck }}" {{ old('default_combo', $contract->default_combo ?: 'chaqueta')===$ck?'selected':'' }}>{{ $cc['label'] }}</option>
                    @endforeach
                </select>
                <div class="small text-secondary mt-1">Los alumnos que agregues heredan este combo (puedes cambiarlo por alumno).</div>
            </div>
            <div class="col-md-6"><label class="form-label small">Código (opcional — se genera solo)</label><input type="text" name="code" class="form-control" value="{{ old('code', $contract->code) }}" placeholder="CTR-{{ date('Y') }}-001"></div>

            <div class="col-md-6"><label class="form-label small">Precio por unidad</label><input type="number" step="0.01" name="price_per_unit" class="form-control" value="{{ old('price_per_unit', $contract->price_per_unit) }}"></div>
            <div class="col-md-6">
                <label class="form-label small">Estado de fabricación</label>
                <select name="production_status" class="form-select">
                    @foreach($stages as $k=>$label)<option value="{{ $k }}" {{ old('production_status', $contract->production_status)==$k?'selected':'' }}>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-12"><label class="form-label small">Notas internas</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $contract->notes) }}</textarea></div>
        </div>

        <hr class="my-4" style="border-color:var(--g-line)">
        <h6 class="mb-1"><i class="bi bi-file-earmark-text"></i> Datos para el contrato (se auto-rellenan en el documento)</h6>
        <p class="small text-secondary">Estos campos completan la plantilla del contrato que firman los acudientes.</p>
        <div class="row g-3">
            <div class="col-12"><label class="form-label small">Objeto (cláusula primera)</label><textarea name="objeto" class="form-control" rows="2" placeholder="Ej: Diseño y confección de 17 chaquetas beisboleras y 18 busos para el grado once del Colegio X.">{{ old('objeto', $contract->objeto) }}</textarea></div>
            <div class="col-md-4"><label class="form-label small">Valor combo (por unidad)</label><input type="number" step="0.01" name="combo_value" class="form-control" value="{{ old('combo_value', $contract->combo_value) }}" placeholder="310500"></div>
            <div class="col-md-4"><label class="form-label small">Valor total del contrato</label><input type="number" step="0.01" name="contract_total" class="form-control" value="{{ old('contract_total', $contract->contract_total) }}" placeholder="5589000"></div>
            <div class="col-md-4"><label class="form-label small">Días hábiles de entrega</label><input type="number" name="delivery_business_days" class="form-control" value="{{ old('delivery_business_days', $contract->delivery_business_days ?? 35) }}" placeholder="35"><div class="small text-secondary mt-1">Se calcula con festivos de Colombia desde la firma.</div></div>
            <div class="col-md-4"><label class="form-label small">O fecha exacta (opcional)</label><input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', optional($contract->delivery_date)->format('Y-m-d')) }}"><div class="small text-secondary mt-1">Si la pones, manda sobre los días hábiles.</div></div>
            <div class="col-12"><label class="form-label small">Valor en letras</label><input type="text" name="total_words" class="form-control" value="{{ old('total_words', $contract->total_words) }}" placeholder="Cinco millones quinientos ochenta y nueve mil pesos"></div>
            <div class="col-12"><label class="form-label small">Detalle y bordados de las prendas (cláusula quinta)</label><textarea name="product_details" class="form-control" rows="6" placeholder="CHAQUETA — Estilo: ... Tela: ... Bordados: Frente..., Manga..., Espalda...">{{ old('product_details', $contract->product_details) }}</textarea></div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

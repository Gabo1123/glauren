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

        <div class="g-card__title mt-3" style="color:var(--g-gold)">Toma de medidas (cm)</div>
        <div class="row g-3 mb-2">
            <div class="col-md-4"><label class="form-label small">Contorno de pecho</label><input type="number" step="0.1" name="chest" class="form-control" value="{{ old('chest', $student->chest) }}"></div>
            <div class="col-md-4"><label class="form-label small">Contorno de cintura</label><input type="number" step="0.1" name="waist" class="form-control" value="{{ old('waist', $student->waist) }}"></div>
            <div class="col-md-4"><label class="form-label small">Contorno de cadera</label><input type="number" step="0.1" name="hip" class="form-control" value="{{ old('hip', $student->hip) }}"></div>
            <div class="col-md-6"><label class="form-label small">Largo de manga</label><input type="number" step="0.1" name="sleeve_length" class="form-control" value="{{ old('sleeve_length', $student->sleeve_length) }}"></div>
            <div class="col-md-6"><label class="form-label small">Largo de talle</label><input type="number" step="0.1" name="talle_length" class="form-control" value="{{ old('talle_length', $student->talle_length) }}"></div>
        </div>

        <div class="g-card__title mt-3" style="color:var(--g-gold)">Pago</div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Estado</label><select name="payment_status" class="form-select"><option value="pendiente" {{ $student->payment_status=='pendiente'?'selected':'' }}>Pendiente</option><option value="pagado" {{ $student->payment_status=='pagado'?'selected':'' }}>Pagado</option></select></div>
            <div class="col-md-6"><label class="form-label small">Valor</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $student->amount) }}"></div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button> <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-outline-light">Cancelar</a></div>
    </form>
</div>
@endsection

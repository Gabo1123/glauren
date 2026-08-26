@extends('layouts.admin')
@section('title', 'Ranking de colegios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Ranking de colegios</h3>
        <div class="text-secondary small">Colegios que la gente agrega y vota. Modera nombres, ajusta votos u oculta/elimina.</div>
    </div>
    <a href="{{ route('ranking') }}" target="_blank" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-up-right"></i> Ver ranking</a>
</div>

<div class="card p-3">
    @if($entries->isEmpty())
        <div class="text-center text-secondary py-4">Aún no hay colegios en el ranking.</div>
    @else
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>#</th><th>Colegio</th><th>Ciudad</th><th style="width:110px">Votos</th><th></th></tr></thead>
        <tbody>
        @foreach($entries as $i => $e)
            <tr class="{{ $e->active ? '' : 'opacity-50' }}">
                <td class="text-secondary">{{ $i+1 }}</td>
                <td><input form="rk{{ $e->id }}" type="text" name="name" value="{{ $e->name }}" class="form-control form-control-sm" style="min-width:180px"></td>
                <td><input form="rk{{ $e->id }}" type="text" name="city" value="{{ $e->city }}" class="form-control form-control-sm" style="min-width:120px"></td>
                <td><input form="rk{{ $e->id }}" type="number" name="votes" value="{{ $e->votes }}" min="0" class="form-control form-control-sm"></td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        <form id="rk{{ $e->id }}" method="POST" action="{{ route('admin.ranking.update', $e) }}" class="d-inline">@csrf @method('PUT')
                            <button class="btn btn-sm btn-primary" title="Guardar cambios"><i class="bi bi-save"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.ranking.toggle', $e) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-light" title="{{ $e->active ? 'Ocultar' : 'Mostrar' }}"><i class="bi {{ $e->active ? 'bi-eye' : 'bi-eye-slash' }}"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.ranking.destroy', $e) }}" class="d-inline" onsubmit="return confirm('¿Eliminar {{ $e->name }} del ranking?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection

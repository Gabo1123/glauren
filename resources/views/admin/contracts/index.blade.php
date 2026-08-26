@extends('layouts.admin')
@section('title', 'Contratos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Contratos</h3>
    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo contrato</a>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Código</th><th>Colegio</th><th>Año</th><th>Alumnos</th><th>Fabricación</th><th>Contrato</th><th></th></tr></thead>
            <tbody>
            @forelse($contracts as $c)
                <tr style="cursor:pointer" onclick="location.href='{{ route('admin.contracts.show', $c) }}'">
                    <td><code>{{ $c->code }}</code></td>
                    <td>{{ $c->school?->name ?? '—' }}</td>
                    <td>{{ $c->year }}</td>
                    <td>{{ $c->students_count }}</td>
                    <td><span class="badge bg-primary">{{ $c->stageLabel() }}</span></td>
                    <td>
                        @php $m=['borrador'=>'secondary','enviado'=>'warning','firmado'=>'success']; @endphp
                        <span class="badge bg-{{ $m[$c->contract_status] ?? 'secondary' }}">{{ $c->contract_status }}</span>
                    </td>
                    <td class="text-end"><i class="bi bi-chevron-right text-secondary"></i></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">Aún no hay contratos. Crea el primero (por ejemplo, uno que llegó por WhatsApp).</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

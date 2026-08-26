@extends('layouts.admin')
@section('title', 'Pagos por aprobar')

@section('content')
<div class="mb-3">
    <h3 class="mb-1">Pagos por aprobar</h3>
    <div class="text-secondary small">Pagos que los estudiantes registraron (efectivo, Nequi o Bancolombia). Revisa el comprobante y aprueba o rechaza.</div>
</div>

<div class="card p-3">
    @if($students->isEmpty())
        <div class="text-center text-secondary py-4">
            <i class="bi bi-inbox" style="font-size:30px"></i>
            <p class="mt-2 mb-0">No hay pagos pendientes de aprobación.</p>
        </div>
    @else
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>Estudiante</th><th>Colegio / contrato</th><th>Método</th><th>Comprobante</th><th>Registrado</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($students as $s)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $s->name }}</div>
                    <div class="small text-secondary">Doc: {{ $s->document }}</div>
                </td>
                <td>
                    <div class="small">{{ $s->contract->school->name ?? '—' }}</div>
                    <div class="small text-secondary">{{ $s->contract->code ?? '' }}</div>
                </td>
                <td>
                    @php $ic = ['efectivo'=>'💵','nequi'=>'📲','bancolombia'=>'🏦']; @endphp
                    <span class="badge bg-secondary">{{ $ic[$s->payment_method] ?? '' }} {{ $s->methodLabel() }}</span>
                </td>
                <td>
                    @if($s->proofUrl())
                        <a href="{{ $s->proofUrl() }}" target="_blank"><img src="{{ $s->proofUrl() }}" style="height:52px;border-radius:6px;border:1px solid var(--g-line)"></a>
                    @else
                        <span class="text-secondary small">Sin comprobante (efectivo)</span>
                    @endif
                </td>
                <td class="small text-secondary">{{ optional($s->payment_declared_at)->diffForHumans() }}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        <form method="POST" action="{{ route('admin.approvals.approve', $s) }}" onsubmit="return confirm('¿Confirmar que recibiste este pago?')">@csrf
                            <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.approvals.reject', $s) }}" onsubmit="return confirm('¿Rechazar este pago? El estudiante deberá registrarlo de nuevo.')">@csrf
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Rechazar</button>
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

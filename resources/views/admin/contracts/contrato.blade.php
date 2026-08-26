@extends('layouts.admin')
@section('title', 'Contrato '.$contract->code)

@section('content')
<a href="{{ route('admin.contracts.show', $contract) }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al contrato</a>
<h3 class="my-3">Contrato {{ $contract->code }}</h3>

@php $m=['borrador'=>'secondary','enviado'=>'warning','firmado'=>'success']; @endphp
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="badge bg-{{ $m[$contract->contract_status] ?? 'secondary' }}">Estado: {{ $contract->contract_status }}</span>
    @if($contract->signed_at)<span class="text-secondary small">Firmado por {{ $contract->signed_by_name }} (CC {{ $contract->signed_by_document }}) el {{ $contract->signed_at->format('d/m/Y H:i') }}</span>@endif
</div>

@if($contract->sign_token && $contract->contract_status !== 'firmado')
    <div class="card p-3 mb-3">
        <div class="g-card__title" style="color:var(--g-gold)">Enlace para que el colegio firme</div>
        <div class="input-group">
            <input type="text" class="form-control" id="signlink" value="{{ route('contrato.firmar', $contract->sign_token) }}" readonly>
            <button class="btn btn-outline-light" onclick="navigator.clipboard.writeText(document.getElementById('signlink').value);this.innerHTML='<i class=\'bi bi-check\'></i> Copiado'"><i class="bi bi-clipboard"></i> Copiar</button>
            @php $schoolWa = $contract->school?->contact_phone ? preg_replace('/\D/','',$contract->school->contact_phone) : null; if($schoolWa && strlen($schoolWa)===10) $schoolWa='57'.$schoolWa;
                $msg = 'Hola '.($contract->school?->name ?? '').'! Ya está listo el contrato de la promoción '.$contract->year.' (código '.$contract->code.'). Léelo y fírmalo en línea aquí: '.route('contrato.firmar', $contract->sign_token); @endphp
            <a class="btn btn-success" target="_blank" rel="noopener" href="https://wa.me/{{ $schoolWa }}?text={{ urlencode($msg) }}"><i class="bi bi-whatsapp"></i> Enviar al colegio</a>
        </div>
    </div>
@endif

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="g-card__title mb-0" style="color:var(--g-gold)">Texto del contrato</div>
        <div class="d-flex gap-2">
            <a href="{{ route('contrato.firmar', $contract->sign_token ?? 'preview') }}" class="btn btn-outline-light btn-sm {{ $contract->sign_token ? '' : 'disabled' }}" target="_blank"><i class="bi bi-eye"></i> Vista previa</a>
            <form method="POST" action="{{ route('admin.contracts.send', $contract) }}" onsubmit="return confirm('¿Dejar el contrato listo para firmar?')">@csrf
                <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> {{ $contract->sign_token ? 'Regenerar enlace' : 'Enviar a firmar' }}</button>
            </form>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.contracts.terms', $contract) }}">
        @csrf @method('PUT')
        <p class="text-secondary small">Edita el borrador (busca los marcadores <code>[EDITA: ...]</code> y complétalos). Permite HTML.</p>
        <textarea name="terms" class="form-control" rows="20" style="font-family:monospace;font-size:13px">{{ old('terms', $contract->terms) }}</textarea>
        <button class="btn btn-primary mt-3"><i class="bi bi-save"></i> Guardar contrato</button>
    </form>
</div>
@endsection

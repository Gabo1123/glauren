@extends('portal.layout')
@section('title', $school->name)

@section('content')
<h1 class="p-h">Hola, {{ $school->name }}</h1>
<p class="p-sub">Aquí ves tus contratos, el avance de fabricación, las fotos del proceso y tu lista de alumnos.</p>

@php
    $badge = [
        'cotizado'=>'#8a93a2','aprobado'=>'#4f8cff','toma_medidas'=>'#e0a422',
        'en_produccion'=>'#e0a422','control_calidad'=>'#4f8cff','listo'=>'#25d366','entregado'=>'#25d366',
    ];
@endphp

@if($contracts->isEmpty())
    <div style="background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:40px;text-align:center;color:var(--g-muted)">
        <i class="bi bi-inbox" style="font-size:34px;display:block;margin-bottom:10px;opacity:.6"></i>
        Todavía no tienes contratos registrados. El equipo Glauren los cargará pronto.
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px">
        @foreach($contracts as $c)
            <a href="{{ route('portal.contract', $c) }}" style="text-decoration:none;color:inherit;background:var(--g-surface);border:1px solid var(--g-line);border-radius:var(--g-r);padding:22px;display:block;transition:var(--g-t)"
               onmouseover="this.style.borderColor='var(--g-gold)'" onmouseout="this.style.borderColor='var(--g-line)'">
                <div style="display:flex;justify-content:space-between;align-items:start;gap:10px;margin-bottom:14px">
                    <div>
                        <div style="font-family:var(--g-font-display);text-transform:uppercase;letter-spacing:.03em;font-size:18px">{{ $c->code }}</div>
                        <div style="color:var(--g-muted);font-size:13px">{{ $c->jacketType?->name ?? 'Chaqueta' }} · {{ $c->year }}</div>
                    </div>
                    <span style="font-size:11px;font-family:var(--g-font-label);letter-spacing:.06em;text-transform:uppercase;color:{{ $badge[$c->production_status] ?? '#8a93a2' }};border:1px solid {{ $badge[$c->production_status] ?? '#8a93a2' }}55;padding:4px 8px;border-radius:20px;white-space:nowrap">{{ $c->stageLabel() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--g-muted)">
                    <span><i class="bi bi-people"></i> {{ $c->students_count }} alumno{{ $c->students_count == 1 ? '' : 's' }}</span>
                    <span>
                        @if($c->contract_status === 'firmado')
                            <span style="color:var(--g-ok)"><i class="bi bi-patch-check-fill"></i> Firmado</span>
                        @elseif($c->contract_status === 'enviado')
                            <span style="color:var(--g-warn)"><i class="bi bi-pen"></i> Por firmar</span>
                        @endif
                        <i class="bi bi-arrow-right" style="margin-left:8px"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection

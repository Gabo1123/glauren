@extends('layouts.admin')
@section('title', 'Tablero de ventas')

@section('content')
@php
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $short = fn ($v) => $v >= 1000000 ? '$'.number_format($v / 1000000, 1, ',', '.').' M' : ($v >= 1000 ? '$'.number_format($v / 1000, 0, ',', '.').' mil' : '$'.number_format($v, 0, ',', '.'));
    $maxMonth = max(1, max(array_column($byMonth, 'created') ?: [1]));
    $maxValue = max(1, max(array_column($byMonth, 'won_value') ?: [1]));
    $maxSrc = max(1, $bySource->max('count') ?: 1);
    $maxPipe = max(1, $pipeline->max('count') ?: 1);
@endphp
<style>
    .kp{background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:12px;padding:14px 16px;height:100%}
    .kp .l{font-size:11.5px;text-transform:uppercase;letter-spacing:.06em;color:var(--g-muted,#9a9aa7)}
    .kp .v{font-size:26px;font-weight:800;line-height:1.15;margin-top:4px}
    .kp .s{font-size:12px;color:var(--g-muted,#9a9aa7)}
    .bars{display:flex;align-items:flex-end;gap:10px;height:190px;padding-top:10px}
    .bars .col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;min-width:0}
    .bars .stack{width:100%;max-width:46px;display:flex;flex-direction:column;justify-content:flex-end;height:100%;gap:2px}
    .bars .b{border-radius:5px 5px 0 0;min-height:2px}
    .bars .n{font-size:11px;color:var(--g-muted,#9a9aa7);margin-top:6px;white-space:nowrap}
    .bars .t{font-size:11px;font-weight:700;margin-bottom:3px}
    .hbar{display:grid;grid-template-columns:minmax(120px,190px) 1fr auto;gap:10px;align-items:center;margin-bottom:9px;font-size:13.5px}
    .hbar .tr{background:rgba(255,255,255,.06);border-radius:6px;height:14px;overflow:hidden;position:relative}
    .hbar .fl{height:100%;border-radius:6px}
    .lg{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--g-muted,#9a9aa7);margin-right:14px}
    .lg i{width:10px;height:10px;border-radius:3px;display:inline-block}
    .todo li{padding:7px 0;border-bottom:1px solid var(--g-line);list-style:none;font-size:13.5px}
    .todo li:last-child{border:0}
</style>

<div class="g-topbar">
    <div>
        <h3 class="mb-1">Tablero de ventas</h3>
        <p class="small text-secondary mb-0">Cotizaciones enviadas y ganadas, cuánto vendes por mes y qué fuente trae más clientes.</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <select name="meses" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach([3, 6, 12, 24] as $m)<option value="{{ $m }}" {{ $months == $m ? 'selected' : '' }}>Últimos {{ $m }} meses</option>@endforeach
        </select>
        <a href="{{ route('admin.sales.cartera') }}" class="btn btn-outline-light btn-sm text-nowrap"><i class="bi bi-wallet2"></i> Cartera</a>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">Cotizaciones</div><div class="v">{{ $t['created'] }}</div><div class="s">creadas</div></div></div>
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">Enviadas</div><div class="v">{{ $t['sent'] }}</div><div class="s">llegaron al cliente</div></div></div>
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">Ganadas</div><div class="v" style="color:#25d366">{{ $t['won'] }}</div><div class="s">{{ $t['lost'] }} perdidas</div></div></div>
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">Cierre</div><div class="v">{{ $t['rate'] }}%</div><div class="s">ganadas / enviadas</div></div></div>
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">Vendido</div><div class="v" style="color:var(--g-gold,#e9b83f)">{{ $short($t['won_value']) }}</div><div class="s">promedio {{ $short($t['avg']) }}</div></div></div>
    <div class="col-6 col-lg-2"><div class="kp"><div class="l">En juego</div><div class="v">{{ $short($t['open']) }}</div><div class="s">cotizaciones abiertas</div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="mb-1">Cotizaciones por mes</h6>
            <div><span class="lg"><i style="background:#5b5b6b"></i>Creadas</span><span class="lg"><i style="background:#4ea1ff"></i>Enviadas</span><span class="lg"><i style="background:#25d366"></i>Ganadas</span></div>
            <div class="bars">
                @foreach($byMonth as $m)
                    <div class="col" title="{{ $m['label'] }}: {{ $m['created'] }} creadas, {{ $m['sent'] }} enviadas, {{ $m['won'] }} ganadas">
                        <div class="t">{{ $m['created'] ?: '' }}</div>
                        <div class="stack" style="flex-direction:row;align-items:flex-end;gap:3px">
                            <div class="b" style="flex:1;background:#5b5b6b;height:{{ $m['created'] / $maxMonth * 100 }}%"></div>
                            <div class="b" style="flex:1;background:#4ea1ff;height:{{ $m['sent'] / $maxMonth * 100 }}%"></div>
                            <div class="b" style="flex:1;background:#25d366;height:{{ $m['won'] / $maxMonth * 100 }}%"></div>
                        </div>
                        <div class="n">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <h6 class="mb-1">Valor vendido por mes</h6>
            <div class="small text-secondary">Suma de las cotizaciones ganadas (según su fecha de creación).</div>
            <div class="bars">
                @foreach($byMonth as $m)
                    <div class="col" title="{{ $m['label'] }}: {{ $money($m['won_value']) }}">
                        <div class="t" style="font-size:10px">{{ $m['won_value'] ? $short($m['won_value']) : '' }}</div>
                        <div class="stack"><div class="b" style="background:var(--g-gold,#e9b83f);height:{{ $m['won_value'] / $maxValue * 100 }}%"></div></div>
                        <div class="n">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="mb-3">¿Qué fuente vende más?</h6>
            @forelse($bySource as $s)
                <div class="hbar">
                    <div class="text-truncate" title="{{ $s['name'] }}">{{ $s['name'] }}</div>
                    <div class="tr"><div class="fl" style="width:{{ $s['count'] / $maxSrc * 100 }}%;background:#4ea1ff;opacity:.45;position:absolute;inset:0 auto 0 0"></div><div class="fl" style="width:{{ $s['won'] / $maxSrc * 100 }}%;background:#25d366;position:absolute;inset:0 auto 0 0"></div></div>
                    <div class="small text-nowrap"><b>{{ $s['won'] }}</b>/{{ $s['count'] }} · {{ $s['rate'] }}% · {{ $short($s['won_value']) }}</div>
                </div>
            @empty
                <p class="small text-secondary mb-0">Aún no hay cotizaciones en este periodo.</p>
            @endforelse
            <div class="small text-secondary mt-2"><span class="lg"><i style="background:#4ea1ff;opacity:.45"></i>Cotizaciones</span><span class="lg"><i style="background:#25d366"></i>Ganadas</span></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <h6 class="mb-3">Embudo actual</h6>
            @foreach($pipeline as $p)
                <a href="{{ route('admin.quotes.index', ['status' => $p['key']]) }}" class="hbar text-decoration-none" style="color:inherit">
                    <div>{{ $p['label'] }}</div>
                    <div class="tr"><div class="fl" style="width:{{ $p['count'] / $maxPipe * 100 }}%;background:{{ $p['key'] === 'cerrado' ? '#25d366' : ($p['key'] === 'cancelado' ? '#ff6b6b' : 'var(--g-gold,#e9b83f)') }}"></div></div>
                    <div class="small fw-bold">{{ $p['count'] }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bi bi-telephone-outbound"></i> A quién llamar hoy</h6>
                <form method="POST" action="{{ route('admin.sales.digest') }}">@csrf<button class="btn btn-outline-light btn-sm"><i class="bi bi-send"></i> Enviarme la lista</button></form>
            </div>
            <ul class="todo p-0 m-0">
                @forelse($today['due']->concat($today['viewed']->whereNotIn('id', $today['due']->pluck('id')))->take(12) as $q)
                    <li class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.quotes.show', $q) }}" class="text-truncate" style="color:inherit">{{ $q->customer_name }} <span class="text-secondary">· {{ $q->code() }}</span></a>
                        <span class="badge {{ $q->status === 'vista' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">{{ $q->status === 'vista' && !$q->needsFollowUp() ? 'Vio y no respondió' : 'Llamar hoy' }}</span>
                    </li>
                @empty
                    <li class="text-secondary">Nada pendiente hoy. 🎉</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-7" id="auto">
        <div class="card p-3 h-100">
            <h6 class="mb-3"><i class="bi bi-robot"></i> Automatizaciones</h6>
            <form method="POST" action="{{ route('admin.sales.automation') }}">
                @csrf @method('PUT')
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="sales_digest" value="1" id="sd" {{ $auto['digest'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="sd"><b>Recordatorio diario (7:00 a. m.)</b> — te llega la lista de a quién llamar a <span class="text-secondary">{{ $auto['email'] ?: 'sin correo configurado' }}</span>@if($auto['phone']) y al WhatsApp {{ $auto['phone'] }}@endif.</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="auto_followup" value="1" id="af" {{ $auto['followup'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="af"><b>Seguimiento automático (10:00 a. m.)</b> — si el cliente vio la cotización y no respondió, se le escribe <b>una sola vez</b> por correo y WhatsApp.
                        @if($today['auto']->count())<span class="badge bg-secondary">{{ $today['auto']->count() }} en espera</span>@endif</label>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-sm-4"><label class="form-label small">Escribir después de</label>
                        <div class="input-group input-group-sm"><input type="number" name="auto_followup_days" min="1" max="15" class="form-control" value="{{ $auto['days'] }}"><span class="input-group-text">días</span></div></div>
                    <div class="col-sm-8"><label class="form-label small">Enlace de reseña en Google (para la encuesta al entregar)</label>
                        <input type="url" name="google_review_url" class="form-control form-control-sm" value="{{ $auto['google'] }}" placeholder="https://g.page/r/…/review"></div>
                </div>
                <label class="form-label small">Mensaje de seguimiento <span class="text-secondary">— usa {nombre}, {numero}, {colegio}, {enlace}</span></label>
                <textarea name="auto_followup_message" rows="3" class="form-control form-control-sm mb-2">{{ $auto['message'] }}</textarea>
                <div class="small text-secondary mb-2"><i class="bi bi-info-circle"></i> WhatsApp solo deja enviar texto libre si el cliente te escribió en las últimas 24 h; si no sale por WhatsApp, queda anotado en el historial para que le escribas tú, y el correo sí sale.</div>
                <button class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Contrato '.$contract->code)

@section('content')
<a href="{{ route('admin.contracts.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Contratos</a>
<div class="d-flex justify-content-between align-items-start my-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">{{ $contract->code }}</h3>
        <div class="text-secondary">{{ $contract->school?->name }} · {{ $contract->year }} · {{ $contract->jacketType?->name ?? 'Sin tipo' }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @php $schoolWa = $contract->school?->contact_phone ? preg_replace('/\D/','',$contract->school->contact_phone) : null; if($schoolWa && strlen($schoolWa)===10) $schoolWa='57'.$schoolWa; @endphp
        @if($schoolWa && $contract->sign_token && $contract->contract_status !== 'firmado')
            <a href="https://wa.me/{{ $schoolWa }}?text={{ urlencode('Hola '.$contract->school->name.'! Aquí está el contrato de la promoción ('.$contract->code.') para revisar y firmar en línea: '.route('contrato.firmar', $contract->sign_token)) }}" target="_blank" rel="noopener" class="btn btn-sm" style="background:#25d366;color:#04240f;font-weight:700"><i class="bi bi-whatsapp"></i> Enviar para firmar</a>
        @endif
        @if($schoolWa)
            <a href="https://wa.me/{{ $schoolWa }}?text={{ urlencode('Hola '.$contract->school->name.'! Tu promoción (contrato '.$contract->code.') va en la etapa: '.$contract->stageLabel().'. Míralo aquí: '.route('portal.login')) }}" target="_blank" rel="noopener" class="btn btn-sm" style="background:#25d366;color:#04240f;font-weight:600"><i class="bi bi-whatsapp"></i> Avisar al colegio</a>
        @endif
        <a href="{{ route('admin.contracts.contract', $contract) }}" class="btn btn-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Contrato / Firma</a>
        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" onsubmit="return confirm('¿Eliminar contrato?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
    </div>
</div>

@php
    // Línea de progreso del contrato (contratación → producción → entrega)
    $cs = $contract->contract_status; $ps = $contract->production_status;
    $flow = ['Borrador','Enviado','Firmado','En producción','Listo','Entregado'];
    $step = $cs === 'enviado' ? 1 : ($cs === 'firmado' ? 2 : 0);
    if ($cs === 'firmado') {
        if (in_array($ps, ['toma_medidas','en_produccion','control_calidad'], true)) $step = 3;
        elseif ($ps === 'listo') $step = 4;
        elseif ($ps === 'entregado') $step = 5;
    }
    $nextHint = [
        0 => 'Siguiente: revisa los términos y pulsa «Contrato / Firma» para generar el enlace y enviarlo al colegio.',
        1 => 'Siguiente: el colegio abre el enlace y firma. Puedes reenviárselo con «Enviar para firmar».',
        2 => 'Firmado ✓. Siguiente: agrega/importa los alumnos y toma sus medidas.',
        3 => 'En producción. Sube fotos del proceso y gestiona los pagos.',
        4 => 'Listo para entregar. Coordina la entrega con el colegio.',
        5 => 'Entregado ✓. ¡Promoción completada!',
    ][$step] ?? '';
@endphp
<div class="card p-3 mb-3">
    <div class="cflow">
        @foreach($flow as $i => $label)
            <div class="cflow-step {{ $i < $step ? 'done' : ($i === $step ? 'now' : '') }}">
                <div class="cflow-dot">{{ $i < $step ? '✓' : $i+1 }}</div>
                <div class="cflow-lbl">{{ $label }}</div>
            </div>
            @if(!$loop->last)<div class="cflow-bar {{ $i < $step ? 'done' : '' }}"></div>@endif
        @endforeach
    </div>
    <div class="small text-secondary mt-2"><i class="bi bi-signpost-2"></i> {{ $nextHint }}</div>
</div>

<style>
    .cflow{ display:flex; align-items:center; }
    .cflow-step{ display:flex; flex-direction:column; align-items:center; gap:6px; flex-shrink:0; }
    .cflow-dot{ width:34px; height:34px; border-radius:50%; display:grid; place-items:center; font-family:var(--g-font-display); font-weight:800; font-size:14px; background:var(--g-surface-3); color:var(--g-muted); border:2px solid var(--g-line); }
    .cflow-lbl{ font-size:11px; color:var(--g-muted); text-transform:uppercase; letter-spacing:.04em; text-align:center; white-space:nowrap; }
    .cflow-step.done .cflow-dot{ background:rgba(37,211,102,.18); color:var(--g-ok); border-color:var(--g-ok); }
    .cflow-step.now .cflow-dot{ background:var(--g-gold); color:#1a1405; border-color:var(--g-gold); }
    .cflow-step.now .cflow-lbl{ color:var(--g-gold); font-weight:700; }
    .cflow-bar{ flex:1; height:3px; background:var(--g-line); margin:0 6px; margin-bottom:22px; border-radius:2px; }
    .cflow-bar.done{ background:var(--g-ok); }
    @media(max-width:640px){ .cflow-lbl{ display:none; } .cflow-bar{ margin-bottom:0; } }
</style>

{{-- Resumen financiero --}}
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="g-card__title mb-0" style="color:var(--g-gold)"><i class="bi bi-cash-stack"></i> Resumen financiero</div>
        <div class="d-flex gap-2 flex-wrap">
            @php
                $pendCount = $contract->students->where('payment_status','!=','pagado')->count();
                $schoolWaPay = $contract->school?->contact_phone ? preg_replace('/\D/','',$contract->school->contact_phone) : null;
                if($schoolWaPay && strlen($schoolWaPay)===10) $schoolWaPay='57'.$schoolWaPay;
            @endphp
            @if($schoolWaPay && $pendCount > 0)
                <a href="https://wa.me/{{ $schoolWaPay }}?text={{ urlencode('Hola '.$contract->school->name.'! Recordatorio de pago del contrato '.$contract->code.': quedan '.$pendCount.' alumno(s) por pagar (total $'.number_format($fin['pending'],0,',','.').'). Pueden pagar en línea buscando su documento en: '.route('seguimiento')) }}" target="_blank" rel="noopener" class="btn btn-sm" style="background:#25d366;color:#04240f;font-weight:600"><i class="bi bi-whatsapp"></i> Recordar al colegio</a>
            @endif
            @php $studWithPhone = $contract->students->whereNotNull('phone')->where('payment_status','!=','pagado')->count(); @endphp
            @if($studWithPhone > 0)
                <form method="POST" action="{{ route('admin.reminders.students', $contract) }}" class="d-inline" onsubmit="return confirm('¿Enviar recordatorio de abono por WhatsApp a {{ $studWithPhone }} estudiante(s)?')">@csrf
                    <button class="btn btn-sm" style="background:#25d366;color:#04240f;font-weight:600"><i class="bi bi-cash-coin"></i> Recordar abono a estudiantes ({{ $studWithPhone }})</button>
                </form>
            @endif
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-plus-lg"></i> Registrar gasto</a>
        </div>
    </div>
    <div class="row g-3 text-center">
        <div class="col-6 col-lg-3">
            <div style="border:1px solid var(--g-line);border-radius:10px;padding:14px">
                <div class="small text-secondary">Ingresos esperados</div>
                <div style="font-family:var(--g-font-display);font-size:24px">${{ number_format($fin['expected'],0,',','.') }}</div>
                <div class="small" style="color:var(--g-ok)">Recibido: ${{ number_format($fin['received'],0,',','.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div style="border:1px solid var(--g-line);border-radius:10px;padding:14px">
                <div class="small text-secondary">Gastos del contrato</div>
                <div style="font-family:var(--g-font-display);font-size:24px;color:#e0736b">${{ number_format($fin['expenses'],0,',','.') }}</div>
                <div class="small text-secondary">Facturas asignadas</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div style="border:1px solid {{ $fin['profit']>=0 ? 'rgba(37,211,102,.4)' : 'rgba(224,115,107,.4)' }};border-radius:10px;padding:14px">
                <div class="small text-secondary">Utilidad estimada</div>
                <div style="font-family:var(--g-font-display);font-size:24px;color:{{ $fin['profit']>=0 ? 'var(--g-ok)' : '#e0736b' }}">${{ number_format($fin['profit'],0,',','.') }}</div>
                <div class="small text-secondary">Margen {{ $fin['margin'] }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div style="border:1px solid var(--g-line);border-radius:10px;padding:14px">
                <div class="small text-secondary">Pendiente de cobro</div>
                <div style="font-family:var(--g-font-display);font-size:24px;color:var(--g-warn)">${{ number_format($fin['pending'],0,',','.') }}</div>
                <div class="small text-secondary">{{ $contract->students->where('payment_status','!=','pagado')->count() }} alumno(s)</div>
            </div>
        </div>
    </div>
    <div class="small text-secondary mt-2">Ingresos = valor por alumno. Gastos = facturas asignadas a este contrato en <a href="{{ route('admin.expenses.index') }}">Gastos</a>. Utilidad = ingresos esperados − gastos.</div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4 h-100 d-flex flex-column">
            <div class="g-card__title" style="color:var(--g-gold)">Estado de fabricación</div>
            <form method="POST" action="{{ route('admin.contracts.status', $contract) }}" class="d-flex gap-2 align-items-center">
                @csrf @method('PUT')
                <select name="production_status" class="form-select">
                    @foreach($stages as $k=>$label)<option value="{{ $k }}" {{ $contract->production_status==$k?'selected':'' }}>{{ $label }}</option>@endforeach
                </select>
                <button class="btn btn-primary text-nowrap"><i class="bi bi-check-lg"></i> Actualizar</button>
            </form>
            <div class="text-secondary small mt-auto pt-3">Este estado es el que ve el alumno en su seguimiento.</div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <div class="g-card__title" style="color:var(--g-gold)">Agregar alumno</div>
            <form method="POST" action="{{ route('admin.students.store', $contract) }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="document" class="form-control form-control-sm" placeholder="Tarjeta de identidad *" required></div>
                    <div class="col-md-6"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nombre *" required></div>
                    <div class="col-md-4"><select name="gender" class="form-select form-select-sm"><option value="">Sexo</option><option value="hombre">Hombre</option><option value="mujer">Mujer</option></select></div>
                    <div class="col-md-4"><input type="text" name="nickname" class="form-control form-control-sm" placeholder="Apodo"></div>
                    <div class="col-md-4"><input type="text" name="size" class="form-control form-control-sm" placeholder="Talla"></div>
                </div>
                <button class="btn btn-primary btn-sm mt-2"><i class="bi bi-plus-lg"></i> Agregar</button>
                <span class="text-secondary small ms-2">Las medidas se completan al editar el alumno.</span>
            </form>
        </div>
    </div>
</div>

@php $jackets = $contract->students->count(); $comps = $contract->jacketType?->componentList() ?? []; @endphp
<div class="card p-4 mt-4" id="prodSheet">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="g-card__title mb-0" style="color:var(--g-gold)"><i class="bi bi-rulers"></i> Materiales a comprar @if($contract->jacketType)· {{ $contract->jacketType->name }}@endif</div>
        <button type="button" onclick="window.print()" class="btn btn-outline-light btn-sm no-print"><i class="bi bi-printer"></i> Imprimir</button>
    </div>

    @if(! $contract->jacketType)
        <p class="text-secondary small mb-0">Asigna un tipo de chaqueta al contrato para calcular los materiales.</p>
    @else
        <div class="d-flex align-items-end gap-3 mb-3 flex-wrap no-print">
            <div>
                <label class="form-label small mb-1">Nº de chaquetas</label>
                <input type="number" id="jkCount" value="{{ max($jackets, 1) }}" min="1" class="form-control form-control-sm" style="width:120px">
                <div class="small text-secondary">{{ $jackets }} alumno(s) cargado(s)</div>
            </div>
            <div>
                <label class="form-label small mb-1">Desperdicio en telas (%)</label>
                <input type="number" id="waste" value="12" min="0" max="60" class="form-control form-control-sm" style="width:120px">
                <div class="small text-secondary">Merma por cortes/encogido</div>
            </div>
        </div>
        <div class="d-none d-print-block mb-2"><b>Chaquetas:</b> <span id="jkPrint">{{ max($jackets, 1) }}</span> · {{ $contract->code }} · {{ $contract->school?->name }}</div>

        <form method="POST" action="{{ route('admin.types.components', $contract->jacketType) }}" id="matForm">
            @csrf @method('PUT')
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr>
                        <th style="min-width:170px">Material</th>
                        <th class="text-center" style="min-width:150px">Por chaqueta</th>
                        <th class="text-end">A comprar<span class="text-secondary">*</span></th>
                        <th class="text-end" style="width:130px">$ / unidad</th>
                        <th class="text-end">Subtotal</th>
                        <th class="no-print" style="width:36px"></th>
                    </tr></thead>
                    <tbody id="matBody">
                    @foreach($comps as $cp)
                        @php $u = $cp['unit'] ?? 'u'; $cost = (int) ($cp['cost'] ?? 0); $qv = rtrim(rtrim(number_format((float)$cp['qty'],2,'.',''),'0'),'.'); @endphp
                        <tr class="mat-row">
                            <td><input type="text" name="name[]" value="{{ $cp['name'] }}" class="form-control form-control-sm" required></td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <input type="number" step="0.01" min="0" name="qty[]" value="{{ $qv }}" class="form-control form-control-sm mat-qty" style="width:78px">
                                    <select name="unit[]" class="form-select form-select-sm mat-unit" style="width:66px">
                                        <option value="m" {{ $u==='m'?'selected':'' }}>m</option>
                                        <option value="u" {{ $u==='u'?'selected':'' }}>u</option>
                                    </select>
                                </div>
                            </td>
                            <td class="text-end prod-buy fw-semibold">—</td>
                            <td class="text-end"><input type="number" step="1" min="0" name="cost[]" value="{{ $cost }}" class="form-control form-control-sm mat-cost text-end" style="width:118px"></td>
                            <td class="text-end fw-bold prod-sub" style="color:var(--g-gold)">—</td>
                            <td class="no-print text-end"><button type="button" class="btn btn-sm btn-outline-danger mat-del" title="Quitar"><i class="bi bi-x"></i></button></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:1px solid var(--g-line)"><td colspan="4" class="text-end text-secondary">Total de tela</td><td class="text-end" id="prodTela">—</td><td class="no-print"></td></tr>
                        <tr style="border-top:2px solid var(--g-gold)"><th colspan="4" class="text-end">Total de la compra</th><th class="text-end" id="prodCost" style="color:var(--g-gold);font-size:18px">—</th><th class="no-print"></th></tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex gap-2 mt-3 no-print">
                <button type="button" id="addMat" class="btn btn-outline-light btn-sm"><i class="bi bi-plus-lg"></i> Agregar material</button>
                <button class="btn btn-primary btn-sm ms-auto"><i class="bi bi-save"></i> Guardar materiales</button>
            </div>
        </form>
        <div class="text-secondary small mt-2 no-print">* Las telas (<b>m</b>) incluyen el % de desperdicio; las unidades (<b>u</b>) se redondean hacia arriba. Al guardar, estos materiales quedan para el modelo <b>{{ $contract->jacketType->name }}</b>.</div>
    @endif
</div>

@if($contract->jacketType)
<style>
    @media print{
        body *{visibility:hidden}
        #prodSheet,#prodSheet *{visibility:visible}
        #prodSheet{position:absolute;left:0;top:0;width:100%}
        #prodSheet .no-print,#prodSheet .mat-del{display:none!important}
        #prodSheet input,#prodSheet select{border:none!important;background:transparent!important;padding:0!important;color:#000!important;-webkit-appearance:none;appearance:none;width:auto!important;text-align:inherit}
        #prodSheet *{color:#000!important}
        body{background:#fff!important}
    }
</style>
<script>
(function(){
    var jk=document.getElementById('jkCount'), wp=document.getElementById('waste'), body=document.getElementById('matBody');
    function fmtN(n){return (Math.round(n*100)/100).toLocaleString('es-CO');}
    function fmtCOP(n){return '$'+Math.round(n).toLocaleString('es-CO');}
    function calc(){
        var n=parseFloat(jk.value)||0, w=(parseFloat(wp.value)||0)/100, tela=0, cost=0;
        body.querySelectorAll('.mat-row').forEach(function(row){
            var q=parseFloat(row.querySelector('.mat-qty').value)||0,
                unit=row.querySelector('.mat-unit').value||'u',
                c=parseFloat(row.querySelector('.mat-cost').value)||0,
                total=q*n, buy;
            if(unit==='m'||unit==='cm'){ buy=Math.ceil(total*(1+w)); tela+=buy; }
            else { buy=Math.ceil(total); }
            row.querySelector('.prod-buy').textContent=fmtN(buy)+' '+unit;
            var sub=buy*c; cost+=sub;
            row.querySelector('.prod-sub').textContent=fmtCOP(sub);
            row.classList.toggle('text-secondary', q<=0);
        });
        document.getElementById('prodTela').textContent=fmtN(tela)+' m';
        document.getElementById('prodCost').textContent=fmtCOP(cost);
        var jp=document.getElementById('jkPrint'); if(jp)jp.textContent=n;
    }
    function newRow(){
        var tr=document.createElement('tr'); tr.className='mat-row';
        tr.innerHTML='<td><input type="text" name="name[]" class="form-control form-control-sm" placeholder="Nuevo material" required></td>'+
            '<td class="text-center"><div class="d-inline-flex gap-1"><input type="number" step="0.01" min="0" name="qty[]" value="1" class="form-control form-control-sm mat-qty" style="width:78px"><select name="unit[]" class="form-select form-select-sm mat-unit" style="width:66px"><option value="m">m</option><option value="u" selected>u</option></select></div></td>'+
            '<td class="text-end prod-buy fw-semibold">—</td>'+
            '<td class="text-end"><input type="number" step="1" min="0" name="cost[]" value="0" class="form-control form-control-sm mat-cost text-end" style="width:118px"></td>'+
            '<td class="text-end fw-bold prod-sub" style="color:var(--g-gold)">—</td>'+
            '<td class="no-print text-end"><button type="button" class="btn btn-sm btn-outline-danger mat-del" title="Quitar"><i class="bi bi-x"></i></button></td>';
        return tr;
    }
    jk.addEventListener('input',calc); wp.addEventListener('input',calc);
    body.addEventListener('input',calc);
    body.addEventListener('click',function(e){ var b=e.target.closest('.mat-del'); if(b){ b.closest('tr').remove(); calc(); } });
    document.getElementById('addMat').addEventListener('click',function(){ body.appendChild(newRow()); calc(); });
    calc();
})();
</script>
@endif

<div class="card p-4 mt-4">
    <div class="g-card__title" style="color:var(--g-gold)">Fotos del proceso (las ve el alumno y el colegio)</div>
    <form method="POST" action="{{ route('admin.contracts.photos.store', $contract) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
        @csrf
        <div class="col-md-5"><label class="form-label small">Fotos (puedes elegir varias)</label><input type="file" name="photos[]" class="form-control form-control-sm" accept="image/*" multiple required></div>
        <div class="col-md-3"><label class="form-label small">Etapa</label><select name="phase" class="form-select form-select-sm"><option value="proceso">Proceso</option><option value="final">Final</option></select></div>
        <div class="col-md-3"><label class="form-label small">Descripción (opcional)</label><input type="text" name="caption" class="form-control form-control-sm"></div>
        <div class="col-md-1"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i></button></div>
    </form>
    @if($contract->photos->count())
        <div class="row g-2">
            @foreach($contract->photos as $ph)
                <div class="col-6 col-md-3">
                    <div style="position:relative">
                        <img src="{{ $ph->url() }}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid var(--g-line)">
                        <span class="badge bg-{{ $ph->phase=='final'?'success':'secondary' }}" style="position:absolute;top:6px;left:6px">{{ $ph->phase }}</span>
                        <form method="POST" action="{{ route('admin.contracts.photos.destroy', [$contract, $ph]) }}" style="position:absolute;top:6px;right:6px" onsubmit="return confirm('¿Eliminar foto?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger py-0 px-1"><i class="bi bi-x"></i></button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary small mb-0">Aún no hay fotos. Sube las del avance de producción.</p>
    @endif
</div>

<div class="card p-3 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <h5 class="mb-0">Alumnos <span class="text-secondary">({{ $contract->students->count() }})</span></h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.students.measures', $contract) }}" class="btn btn-sm btn-primary"><i class="bi bi-table"></i> Tomar medidas (tabla)</a>
            <a href="{{ route('admin.students.template', $contract) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-filetype-csv"></i> Plantilla</a>
            <a href="{{ route('admin.students.export', $contract) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-download"></i> Exportar</a>
            <button class="btn btn-sm btn-outline-light" type="button" onclick="document.getElementById('impForm').classList.toggle('d-none')"><i class="bi bi-upload"></i> Importar CSV</button>
        </div>
    </div>
    <form id="impForm" method="POST" action="{{ route('admin.students.import', $contract) }}" enctype="multipart/form-data" class="d-none mb-3 p-3" style="background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:8px">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-8"><label class="form-label small">Archivo CSV (columnas: documento, nombre, sexo, apodo, talla)</label><input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required></div>
            <div class="col-md-4"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-check-lg"></i> Subir e importar</button></div>
        </div>
        <div class="small text-secondary mt-2">Descarga primero la <a href="{{ route('admin.students.template', $contract) }}">plantilla</a>, llénala en Excel y súbela. Los documentos repetidos se omiten.</div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Documento</th><th>Nombre</th><th>Sexo</th><th>Talla</th><th>Medidas</th><th>Pago</th><th>WhatsApp / Avisos</th><th></th></tr></thead>
            <tbody>
            @forelse($contract->students as $st)
                <tr>
                    <td><code>{{ $st->document }}</code></td>
                    <td>{{ $st->name }} @if($st->nickname)<span class="text-secondary small">({{ $st->nickname }})</span>@endif</td>
                    <td class="text-secondary">{{ $st->gender ?? '—' }}</td>
                    <td>{{ $st->size ?: '—' }}</td>
                    <td>{!! $st->hasMeasurements() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">Faltan</span>' !!}</td>
                    <td>{!! $st->isPaid() ? '<span class="badge bg-success">Pagado</span>' : '<span class="badge bg-warning">Pendiente</span>' !!}</td>
                    <td>
                        @if($st->phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/','',$st->phone) }}" target="_blank" style="color:#25d366" class="small text-decoration-none"><i class="bi bi-whatsapp"></i> {{ $st->phone }}</a>
                            <form method="POST" action="{{ route('admin.students.notify', [$contract, $st]) }}" class="d-inline">@csrf @method('PUT')
                                @if($st->notify_optin)
                                    <button class="btn btn-sm btn-success py-0 px-2 ms-1" title="Recibe avisos · clic para desactivar"><i class="bi bi-bell-fill"></i></button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" title="Avisos apagados · clic para activar"><i class="bi bi-bell-slash"></i></button>
                                @endif
                            </form>
                        @else
                            <span class="text-secondary small">Sin número</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.students.pay', [$contract, $st]) }}" class="d-inline">@csrf @method('PUT')
                            <button class="btn btn-sm {{ $st->isPaid() ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $st->isPaid() ? 'Marcar pendiente' : 'Marcar pagado' }}"><i class="bi bi-cash-coin"></i></button>
                        </form>
                        <a href="{{ route('admin.students.edit', [$contract, $st]) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-rulers"></i> Medidas</a>
                        <form method="POST" action="{{ route('admin.students.destroy', [$contract, $st]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar alumno?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">Aún no hay alumnos. Agrégalos arriba o súbelos por lista (próximamente).</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Firmantes del contrato --}}
<div class="card p-3 mt-4">
    @php $signed = $contract->signers->where('signed_at','!=',null)->count(); $totalSign = $contract->signers->count(); @endphp
    <h5 class="mb-2"><i class="bi bi-pen"></i> Firmantes del contrato @if($totalSign)<span class="badge {{ $signed===$totalSign ? 'bg-success' : 'bg-warning text-dark' }}">{{ $signed }}/{{ $totalSign }} firmaron</span>@endif</h5>
    <p class="small text-secondary">Agrega los acudientes que deben firmar (papá, mamá, etc.). Cada uno recibe su <b>enlace único</b> y firma con un código por WhatsApp.</p>
    @if($contract->signers->count())
    <div class="table-responsive mb-3">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Rol</th><th>WhatsApp</th><th>Estado</th><th class="text-end">Enlace</th></tr></thead>
            <tbody>
            @foreach($contract->signers as $sg)
                <tr>
                    <td>{{ $sg->name }}</td>
                    <td class="text-secondary small">{{ $sg->role ?: '—' }}</td>
                    <td class="small">{{ $sg->phone ?: '—' }}</td>
                    <td>{!! $sg->hasSigned() ? '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Firmó</span>' : '<span class="text-warning">Pendiente</span>' !!}</td>
                    <td class="text-end" style="white-space:nowrap">
                        @if(!$sg->hasSigned() && $sg->phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/','',$sg->phone) }}?text={{ urlencode('Hola '.$sg->name.', firma el contrato de la promoción aquí: '.$sg->signUrl()) }}" target="_blank" class="btn btn-sm" style="background:#25d366;color:#04240f"><i class="bi bi-whatsapp"></i> Enviar enlace</a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="navigator.clipboard.writeText('{{ $sg->signUrl() }}');this.textContent='✓ Copiado'"><i class="bi bi-link-45deg"></i></button>
                        <form method="POST" action="{{ route('admin.contracts.signer.delete', [$contract, $sg]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar firmante?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <form method="POST" action="{{ route('admin.contracts.signer.add', $contract) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4"><label class="form-label small">Nombre del acudiente *</label><input type="text" name="name" class="form-control form-control-sm" required></div>
        <div class="col-md-3"><label class="form-label small">Rol</label><input type="text" name="role" class="form-control form-control-sm" placeholder="Papá / Mamá"></div>
        <div class="col-md-3"><label class="form-label small">WhatsApp</label><input type="text" name="phone" class="form-control form-control-sm" placeholder="3001234567"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Agregar</button></div>
    </form>
</div>

{{-- Votación de diseño --}}
<div class="card p-3 mt-4">
    <h5 class="mb-3"><i class="bi bi-check2-square"></i> Votación de diseño del curso @if($totalVotes)<span class="badge bg-secondary">{{ $totalVotes }} votos</span>@endif</h5>
    <p class="small text-secondary">Sube 2 o 3 opciones de diseño. Los estudiantes votan desde su portal cuál prefieren.</p>

    <form method="POST" action="{{ route('admin.contracts.voting', $contract) }}" class="d-flex gap-4 flex-wrap mb-3 p-2 px-3" style="background:var(--g-surface-2,#22222a);border-radius:8px">
        @csrf @method('PUT')
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="voting_enabled" value="1" id="v_en" {{ $contract->voting_enabled ? 'checked' : '' }}>
            <label class="form-check-label small" for="v_en">Votación activa (los alumnos pueden votar)</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="voting_show_results" value="1" id="v_res" {{ $contract->voting_show_results ? 'checked' : '' }}>
            <label class="form-check-label small" for="v_res">Mostrar la opción ganadora / resultados a los alumnos</label>
        </div>
        <button class="btn btn-sm btn-outline-warning py-0">Guardar</button>
    </form>
    @if($designOptions->count())
    <div class="row g-3 mb-3">
        @foreach($designOptions as $op)
            @php $vpct = $totalVotes ? round($op->votes_count/$totalVotes*100) : 0; @endphp
            <div class="col-6 col-md-4">
                <div class="border rounded p-2" style="border-color:var(--g-line)!important">
                    <img src="{{ $op->imageUrl() }}" alt="{{ $op->label }}" class="img-fluid rounded mb-2" style="width:100%;object-fit:cover;aspect-ratio:1;background:#fff">
                    <div class="d-flex justify-content-between align-items-center">
                        <b class="small">{{ $op->label }}</b>
                        <span class="badge bg-warning text-dark">{{ $op->votes_count }} · {{ $vpct }}%</span>
                    </div>
                    <div class="progress mt-1" style="height:6px"><div class="progress-bar bg-warning" style="width:{{ $vpct }}%"></div></div>
                    <form method="POST" action="{{ route('admin.contracts.design.delete', [$contract, $op]) }}" onsubmit="return confirm('¿Eliminar opción?')" class="mt-2">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger w-100 py-0">Eliminar</button></form>
                </div>
            </div>
        @endforeach
    </div>
    @endif
    <form method="POST" action="{{ route('admin.contracts.design.add', $contract) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5"><label class="form-label small">Nombre de la opción</label><input type="text" name="label" class="form-control form-control-sm" placeholder="Ej: Diseño A"></div>
        <div class="col-md-4"><label class="form-label small">Imagen</label><input type="file" name="image" class="form-control form-control-sm" accept="image/*" required></div>
        <div class="col-md-3"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Agregar opción</button></div>
    </form>
</div>

{{-- Mensajes con el colegio --}}
<div class="card p-3 mt-4">
    <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Mensajes con el colegio</h5>
    <div class="d-flex flex-column gap-2 mb-3" style="max-height:340px;overflow-y:auto">
        @forelse($messages as $m)
            @php $glauren = $m->sender === 'glauren'; @endphp
            <div class="{{ $glauren ? 'ms-auto text-end' : '' }}" style="max-width:78%">
                <div class="p-2 px-3 d-inline-block text-start" style="background:{{ $glauren ? 'var(--g-gold,#c9a54b)' : 'var(--g-surface-2,#22222a)' }};color:{{ $glauren ? '#1a1405' : '#e7e3d8' }};border-radius:12px">{!! nl2br(e($m->body)) !!}</div>
                <div class="small text-secondary mt-1">{{ $glauren ? 'Glauren (tú)' : ($contract->school?->name ?? 'Colegio') }} · {{ $m->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <p class="text-secondary small text-center py-3 mb-0">No hay mensajes con este colegio todavía.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('admin.contracts.reply', $contract) }}" class="d-flex gap-2">
        @csrf
        <input type="text" name="body" maxlength="1000" required class="form-control" placeholder="Responder al colegio…">
        <button class="btn btn-primary"><i class="bi bi-send"></i> Enviar</button>
    </form>
</div>
@endsection

@extends('layouts.admin')
@section('title', $quote->exists ? 'Editar cotización '.$quote->code() : $L['new_title'])

@php
    use App\Modules\Chaquetas\Models\Quote;
    // Filas: lo que se envió (si hubo error) o lo guardado.
    $rows = old('lines')
        ? array_values(array_map(fn ($l) => [
            'garment' => $l['garment'] ?? '', 'name' => $l['name'] ?? '', 'features' => $l['features'] ?? '',
            'qty' => $l['qty'] ?? 1, 'unit_price' => $l['unit_price'] ?? '', 'photo' => $l['photo_path'] ?? null,
            'photo_url' => ! empty($l['photo_path']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($l['photo_path']) : null,
        ], old('lines')))
        : array_map(fn ($l) => array_merge($l, ['features' => implode("\n", $l['features'])]), $lines);
    if (! $rows) {
        $rows = [['garment' => $L['garment'], 'name' => '', 'features' => '', 'qty' => $L['default_qty'], 'unit_price' => '', 'photo' => null, 'photo_url' => null]];
    }
@endphp

@section('content')
<style>
.bz-line{background:var(--g-surface-2);border:1px solid var(--g-line);border-radius:16px;padding:18px;position:relative}
.bz-line+.bz-line{margin-top:14px}
.bz-n{position:absolute;left:-12px;top:-12px;width:32px;height:32px;border-radius:50%;background:#FFC72C;color:#0E0E0E;display:grid;place-items:center;font-weight:800}
.bz-photo{width:100%;aspect-ratio:1/1;border:2px dashed var(--g-line-strong);border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;cursor:pointer;overflow:hidden;position:relative;color:var(--g-muted);text-align:center;font-size:12px;background:#0f1218}
.bz-photo:hover{border-color:#FFC72C;color:#FFC72C}
.bz-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.bz-photo input{display:none}
.bz-sub{font-size:13px;color:var(--g-muted)}
.bz-sub b{color:#FFC72C;font-size:18px}
.bz-sum{position:sticky;top:20px}
.bz-sum .big{font-size:30px;font-weight:800;color:#FFC72C;line-height:1.1}
.bz-gift{background:rgba(255,199,44,.1);border:1px solid rgba(255,199,44,.35);border-radius:12px;padding:12px 14px;font-size:13px}
</style>

<a href="{{ $quote->exists ? route('admin.quotes.show', $quote) : route($rp.'.index') }}" class="text-secondary small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
<h3 class="my-3">{{ $quote->exists ? 'Editar cotización '.$quote->code() : $L['new_title'] }}</h3>

@if($errors->any())
    <div class="alert alert-danger small">{{ $errors->first() }}</div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $quote->exists ? route($rp.'.update', $quote) : route($rp.'.store') }}" id="bzForm">
    @csrf
    @if($quote->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card p-4">
                <h6 class="mb-3"><i class="bi {{ $L['icon'] }}" style="color:#FFC72C"></i> {{ $L['entity'] }} y contacto</h6>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label small">{{ $L['entity'] }} *</label><input name="company_name" value="{{ old('company_name', $quote->school) }}" class="form-control" required maxlength="160" placeholder="{{ $L['entity_ph'] }}"></div>
                    @if($L['school'])
                        <div class="col-md-3"><label class="form-label small">Curso / promoción</label><input name="company_course" value="{{ old('company_course', $company['course']) }}" class="form-control" maxlength="80" placeholder="Grado 11 · Prom 2027"></div>
                    @else
                        <div class="col-md-3"><label class="form-label small">NIT</label><input name="company_nit" value="{{ old('company_nit', $company['nit']) }}" class="form-control" maxlength="40"></div>
                    @endif
                    <div class="col-md-3"><label class="form-label small">Ciudad</label><input name="company_city" value="{{ old('company_city', $company['city']) }}" class="form-control" maxlength="120" placeholder="Bogotá"></div>
                    <div class="col-md-4"><label class="form-label small">Contacto *</label><input name="contact_name" value="{{ old('contact_name', $quote->customer_name) }}" class="form-control" required maxlength="120"></div>
                    <div class="col-md-3"><label class="form-label small">Cargo</label><input name="contact_role" value="{{ old('contact_role', $company['role']) }}" class="form-control" maxlength="120" placeholder="{{ $L['role_ph'] }}"></div>
                    <div class="col-md-5"><label class="form-label small">Celular / WhatsApp *</label><input name="contact_phone" value="{{ old('contact_phone', $quote->customer_phone) }}" class="form-control" required maxlength="40" inputmode="tel"></div>
                    <div class="col-md-6"><label class="form-label small">Correo</label><input type="email" name="contact_email" value="{{ old('contact_email', $quote->customer_email) }}" class="form-control" maxlength="160"></div>
                    <div class="col-md-3"><label class="form-label small">Validez (días)</label><input type="number" name="validity" value="{{ old('validity', $company['validity']) }}" min="1" max="365" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label small">Llamar el</label><input type="date" name="follow_up_at" value="{{ old('follow_up_at', ($quote->follow_up_at ?? today()->addDays(2))->toDateString()) }}" class="form-control"></div>
                </div>
            </div>

            <div class="card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="bi bi-bag" style="color:#FFC72C"></i> Prendas que cotizas</h6>
                    <span class="small text-secondary">Cada prenda sale en el PDF con su foto, características y valor</span>
                </div>
                <div id="bzLines" class="ps-2 pt-2">
                    @foreach($rows as $i => $r)
                        @include('admin.business.partials.line', ['i' => $i, 'r' => $r])
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-light w-100 mt-3" id="bzAdd"><i class="bi bi-plus-circle"></i> Agregar otra prenda</button>
            </div>

            <div class="card p-4 mt-4">
                <label class="form-label small">Notas internas (no salen en el PDF)</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $quote->notes) }}</textarea>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card p-4 bz-sum">
                <h6 class="mb-3">Resumen</h6>
                <div class="d-flex justify-content-between small mb-1"><span class="text-secondary">Prendas</span><b id="sLines">0</b></div>
                <div class="d-flex justify-content-between small mb-3"><span class="text-secondary">{{ $L['units'] }}</span><b id="sUnits">0</b></div>
                <div class="small text-secondary">Valor total</div>
                <div class="big mb-3" id="sTotal">$0</div>
                <div class="bz-gift mb-3">
                    <div class="fw-bold mb-1"><i class="bi bi-gift" style="color:#FFC72C"></i> Obsequios incluidos</div>
                    @foreach($L['gifts'] as $g)<div>✓ {{ $g }}</div>@endforeach
                    <div class="mt-2" id="sGift" style="color:#FFC72C;font-weight:700"></div>
                </div>
                @if(! $quote->exists)
                    <div class="form-check mb-3">
                        <input type="hidden" name="send_email" value="0">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" id="send_email" {{ old('send_email', '1') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="send_email">Enviar por correo al crearla (si tiene correo)</label>
                    </div>
                @endif
                <button class="btn btn-primary w-100 py-2" style="font-weight:700"><i class="bi bi-file-earmark-check"></i> {{ $quote->exists ? 'Guardar cambios' : 'Crear cotización' }}</button>
                <p class="small text-secondary mt-3 mb-0">Después podrás verla como el cliente, descargar el PDF y enviarla por WhatsApp.</p>
            </div>
        </div>
    </div>
</form>

<datalist id="bzGarments">@foreach(Quote::BUSINESS_GARMENTS as $g)<option value="{{ $g }}">@endforeach</datalist>

<template id="bzTpl">
    @include('admin.business.partials.line', ['i' => '__I__', 'r' => ['garment' => $L['garment'], 'name' => '', 'features' => '', 'qty' => $L['default_qty'], 'unit_price' => '', 'photo' => null, 'photo_url' => null]])
</template>

<script>
(function () {
    const box = document.getElementById('bzLines');
    let next = {{ count($rows) }};
    const money = n => '$' + Math.round(n).toLocaleString('es-CO');
    const num = v => parseInt(String(v || '').replace(/\D/g, '') || '0', 10);

    function renumber() {
        box.querySelectorAll('.bz-line').forEach((el, k) => {
            el.querySelector('.bz-n').textContent = k + 1;
            el.querySelector('.bz-del').style.display = box.children.length > 1 ? '' : 'none';
        });
    }
    function recalc() {
        let units = 0, total = 0;
        box.querySelectorAll('.bz-line').forEach(el => {
            const q = num(el.querySelector('[data-qty]').value), p = num(el.querySelector('[data-price]').value);
            units += q; total += q * p;
            el.querySelector('[data-subtotal]').textContent = money(q * p);
        });
        document.getElementById('sLines').textContent = box.children.length;
        document.getElementById('sUnits').textContent = units;
        document.getElementById('sTotal').textContent = money(total);
        const g = Math.floor(units / 25);
        document.getElementById('sGift').textContent = g > 0
            ? '🎁 Este pedido recibe ' + g + ' chaqueta' + (g > 1 ? 's' : '') + ' de obsequio'
            : 'Faltan ' + (25 - units) + ' unidades para la chaqueta de obsequio';
    }
    function bind(el) {
        el.querySelectorAll('[data-qty],[data-price]').forEach(i => i.addEventListener('input', recalc));
        const price = el.querySelector('[data-price]');
        price.addEventListener('blur', () => { const n = num(price.value); price.value = n ? n.toLocaleString('es-CO') : ''; });
        const file = el.querySelector('input[type=file]');
        file.addEventListener('change', async () => {
            let f = file.files[0]; if (!f) return;
            // Reduce las fotos grandes del celular antes de subirlas (máx. 1600 px).
            try {
                if (f.size > 900 * 1024 && window.DataTransfer) {
                    const bmp = await createImageBitmap(f);
                    const k = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
                    const c = document.createElement('canvas'); c.width = Math.round(bmp.width * k); c.height = Math.round(bmp.height * k);
                    c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
                    const blob = await new Promise(r => c.toBlob(r, 'image/jpeg', 0.86));
                    if (blob && blob.size < f.size) {
                        f = new File([blob], f.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                        const dt = new DataTransfer(); dt.items.add(f); file.files = dt.files;
                    }
                }
            } catch (e) {}
            const ph = el.querySelector('.bz-photo');
            let img = ph.querySelector('img'); if (!img) { img = document.createElement('img'); ph.appendChild(img); }
            img.src = URL.createObjectURL(f);
            const rm = el.querySelector('[data-remove]'); if (rm) rm.checked = false;
        });
        el.querySelector('.bz-del').addEventListener('click', () => { el.remove(); renumber(); recalc(); });
    }
    document.getElementById('bzAdd').addEventListener('click', () => {
        const html = document.getElementById('bzTpl').innerHTML.replaceAll('__I__', next++);
        box.insertAdjacentHTML('beforeend', html);
        const el = box.lastElementChild; bind(el); renumber(); recalc();
        el.querySelector('input[name$="[name]"]').focus();
    });
    box.querySelectorAll('.bz-line').forEach(bind);
    renumber(); recalc();
})();
</script>
@endsection

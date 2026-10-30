@php
    use App\Modules\Chaquetas\Support\QuoteCatalog as QC;
    $sel = $selected ?? ['combo' => null, 'products' => [], 'extras' => []];
    $selCombo = old('combo', $sel['combo'] ?? null);
    $selProducts = old('products', $sel['products'] ?? []);
    $selExtras = old('extras', $sel['extras'] ?? []);
@endphp
<label class="form-label small">Combo (opcional)</label>
<select name="combo" class="form-select form-select-sm mb-3">
    <option value="">Sin combo</option>
    @foreach($combos as $c)
        <option value="{{ $c['number'] }}" {{ (string) $selCombo === (string) $c['number'] ? 'selected' : '' }}>
            Combo {{ $c['number'] }} · {{ implode(' + ', $c['names']) }} — {{ QC::money($c['price']) }}
        </option>
    @endforeach
</select>

<label class="form-label small">Prendas sueltas</label>
<div class="row g-1 mb-3">
    @foreach($products as $key => $p)
        <div class="col-md-6">
            <label class="d-flex gap-2 small align-items-start">
                <input type="checkbox" class="form-check-input mt-1" name="products[]" value="{{ $key }}" {{ in_array($key, (array) $selProducts, true) ? 'checked' : '' }}>
                <span>{{ $p['name'] }} <span class="text-secondary">— {{ QC::money($p['price']) }}</span></span>
            </label>
        </div>
    @endforeach
</div>

<label class="form-label small">Adicionales</label>
<div class="row g-1 mb-3">
    @foreach($extras as $key => $e)
        <div class="col-md-6">
            <label class="d-flex gap-2 small align-items-start">
                <input type="checkbox" class="form-check-input mt-1" name="extras[]" value="{{ $key }}" {{ in_array($key, (array) $selExtras, true) ? 'checked' : '' }}>
                <span>{{ $e['name'] }} <span class="text-secondary">— {{ QC::money($e['price']) }}</span></span>
            </label>
        </div>
    @endforeach
</div>

<div class="bz-line">
    <span class="bz-n">{{ is_int($i) ? $i + 1 : '' }}</span>
    <button type="button" class="btn btn-sm btn-outline-light bz-del position-absolute" style="right:12px;top:12px" title="Quitar prenda"><i class="bi bi-trash"></i></button>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="bz-photo" title="Subir foto de la prenda">
                @if(! empty($r['photo_url']))<img src="{{ $r['photo_url'] }}" alt="">@endif
                <i class="bi bi-camera fs-3"></i>
                <span>Subir foto<br><small>JPG, PNG o WEBP · máx. 8 MB</small></span>
                <input type="file" name="lines[{{ $i }}][photo]" accept="image/jpeg,image/png,image/webp">
            </label>
            @if(! empty($r['photo']))
                <input type="hidden" name="lines[{{ $i }}][photo_path]" value="{{ $r['photo'] }}">
                <label class="small text-secondary mt-2 d-flex gap-2 align-items-center"><input type="checkbox" class="form-check-input mt-0" name="lines[{{ $i }}][remove_photo]" value="1" data-remove> Quitar foto</label>
            @endif
        </div>
        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-sm-5">
                    <label class="form-label small">Tipo de prenda</label>
                    <input name="lines[{{ $i }}][garment]" value="{{ $r['garment'] }}" class="form-control form-control-sm" list="bzGarments" maxlength="60">
                </div>
                <div class="col-sm-7">
                    <label class="form-label small">Nombre en la cotización *</label>
                    <input name="lines[{{ $i }}][name]" value="{{ $r['name'] }}" class="form-control form-control-sm" required maxlength="120" placeholder="Ej.: Chaqueta corporativa antifluido">
                </div>
                <div class="col-12">
                    <label class="form-label small">Características <span class="text-secondary">(una por línea)</span></label>
                    <textarea name="lines[{{ $i }}][features]" rows="4" class="form-control form-control-sm" placeholder="Tela Lafayette antifluido&#10;Logo bordado en el pecho&#10;Forro interno y bolsillos con cremallera&#10;Tallas S a XXL">{{ $r['features'] }}</textarea>
                </div>
                <div class="col-4">
                    <label class="form-label small">Cantidad *</label>
                    <input type="number" name="lines[{{ $i }}][qty]" value="{{ $r['qty'] }}" min="1" class="form-control form-control-sm" required data-qty>
                </div>
                <div class="col-4">
                    <label class="form-label small">Valor x unidad *</label>
                    <div class="input-group input-group-sm"><span class="input-group-text" style="background:var(--g-surface-3);color:var(--g-bone);border-color:var(--g-line)">$</span>
                        <input name="lines[{{ $i }}][unit_price]" value="{{ is_numeric($r['unit_price']) && $r['unit_price'] ? number_format($r['unit_price'], 0, ',', '.') : $r['unit_price'] }}" class="form-control" required inputmode="numeric" placeholder="Al por mayor" data-price>
                    </div>
                </div>
                <div class="col-4 d-flex align-items-end">
                    <div class="bz-sub">Subtotal<br><b data-subtotal>$0</b></div>
                </div>
            </div>
        </div>
    </div>
</div>

@extends('layouts.admin')
@section('title', 'Textos del sitio')

@php
    // Parseo de secciones del home para el editor visual (con defaults actuales)
    $parse = function ($text) {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', trim((string) $text)) as $l) {
            $l = trim($l); if ($l === '') { continue; }
            $out[] = array_map('trim', explode('|', $l));
        }
        return $out;
    };
    $garRows = $parse($values['home_gar'] ?? '') ?: [['🛡️','Garantía 1 año','Cubrimos defectos de fábrica todo el año escolar.'],['⚡','Entrega récord','Procesos ágiles, sin demoras ni sustos.'],['🏆','+10 años','Miles de promociones felices en Colombia.'],['✨','Diseños únicos','Bordados de alta calidad · telas Lafayette y Patprimo.']];
    $stepRows = $parse($values['home_steps'] ?? '') ?: [['🎨','Diseña en 3D','Elige el tipo, colores por zona y suma escudos y nombres.'],['✍️','Aprueba','Tu curso revisa el diseño y firmas el contrato en línea.'],['📸','Fabricamos','Sigue la producción con fotos reales, etapa por etapa.'],['📦','Recíbela','Paga en línea seguro y luce tu chaqueta.']];
    $numRows  = $parse($values['home_numbers'] ?? '') ?: [['500','Promociones felices'],['80','Colegios'],['10','Años de experiencia'],['12','Ciudades en Colombia']];
    $colList  = array_values(array_filter(array_map('trim', explode(',', $values['home_trusted'] ?? 'Universidad Católica, Colegio La Giralda, George Williams, Montreal, Generación Enoc, Liceo Freire'))));
@endphp

@section('content')
<h3 class="mb-1">Textos / Home</h3>
<div class="text-secondary small mb-3">Edita cada sección con sus casillas. Al final pulsa <b>Guardar</b>.</div>
<div class="card p-4" style="max-width:820px">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="homeForm">
        @csrf @method('PUT')
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small">Imagen principal del hero (banner)</label>
            <input type="file" name="hero_image" class="form-control" accept="image/*">
            <div class="small text-secondary mt-1">Sube tu banner con el diseño y textos ya integrados. Ideal horizontal (aprox. 1500×1000).</div>
            @php $hi = $values['home_hero_image'] ?? null; @endphp
            @if($hi)<img src="{{ $hi }}" class="mt-2 rounded" style="max-height:120px;border:1px solid var(--g-line)">@endif
        </div>
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small">Banner de “Ya visten Glauren” (sección Promociones)</label>
            <input type="file" name="banner_image" class="form-control" accept="image/*">
            <div class="small text-secondary mt-1">Imagen grande que aparece bajo el título “Ya visten Glauren”. Ideal horizontal (aprox. 1500×1000).</div>
            @php $bi = $values['showcase_banner'] ?? null; @endphp
            @if($bi)<img src="{{ $bi }}" class="mt-2 rounded" style="max-height:120px;border:1px solid var(--g-line)">@endif
        </div>
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small">Marca de agua del diseñador 3D (fondo)</label>
            <input type="file" name="watermark_image" class="form-control" accept="image/*">
            <div class="small text-secondary mt-1">Imagen de fondo con la marca GLAUREN que aparece detrás de la prenda en el diseñador (y en las fotos que descarga el cliente). Ideal un patrón claro, aprox. 1500×1000.</div>
            @php $wm = $values['designer_watermark'] ?? null; @endphp
            @if($wm)<img src="{{ $wm }}" class="mt-2 rounded" style="max-height:120px;border:1px solid var(--g-line);background:#fff">@endif
        </div>
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small">Logo del menú</label>
            <input type="file" name="logo_image" class="form-control" accept="image/*">
            <div class="small text-secondary mt-1">PNG con fondo transparente, horizontal. Reemplaza la marca de texto en el menú.</div>
            @php $lg = $values['brand_logo'] ?? null; @endphp
            @if($lg)<img src="{{ $lg }}" class="mt-2 rounded" style="max-height:60px;background:#14171e;padding:6px;border:1px solid var(--g-line)">@endif
        </div>
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-2"><i class="bi bi-credit-card"></i> Métodos de pago que ve el estudiante</label>
            @php
                $allowEf = ($values['pay_allow_efectivo'] ?? '0') === '1';
                $allowNe = ($values['pay_allow_nequi'] ?? '1') === '1';
                $allowBa = ($values['pay_allow_bancolombia'] ?? '1') === '1';
            @endphp
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="pay_allow_efectivo" value="1" id="pe" {{ $allowEf ? 'checked' : '' }}><label class="form-check-label small" for="pe">💵 Efectivo (día de entrega) <span class="text-secondary">— apagado por defecto</span></label></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="pay_allow_nequi" value="1" id="pn" {{ $allowNe ? 'checked' : '' }}><label class="form-check-label small" for="pn">📲 Nequi</label></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="pay_allow_bancolombia" value="1" id="pb" {{ $allowBa ? 'checked' : '' }}><label class="form-check-label small" for="pb">🏦 Bancolombia</label></div>

            <label class="form-label small d-block mt-3 mb-2">Plan de abonos</label>
            <div class="row g-2">
                <div class="col-6"><label class="small text-secondary">% al inicio</label><input type="number" name="pay_initial_pct" class="form-control" min="0" max="100" value="{{ $values['pay_initial_pct'] ?? 0 }}"></div>
                <div class="col-6"><label class="small text-secondary">% al final (día de entrega)</label><input type="number" name="pay_final_pct" class="form-control" min="0" max="100" value="{{ $values['pay_final_pct'] ?? 50 }}"></div>
            </div>
            <div class="small text-secondary mt-1">El estudiante paga <b>0%</b> para empezar y el <b>%</b> final el día de la entrega. El efectivo aplica solo al pago final.</div>
        </div>
        {{-- ===== Editor visual de secciones del home ===== --}}
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-2">🛡️ Garantías <span class="text-secondary">(los chips del home)</span></label>
            <div id="repGar">
                @foreach($garRows as $i => $g)
                <div class="rep-row d-flex gap-2 mb-2">
                    <input type="text" name="gar[{{ $i }}][emoji]" class="form-control form-control-sm text-center" style="max-width:56px" value="{{ $g[0] ?? '' }}" placeholder="🛡️">
                    <input type="text" name="gar[{{ $i }}][title]" class="form-control form-control-sm" style="max-width:190px" value="{{ $g[1] ?? '' }}" placeholder="Título">
                    <input type="text" name="gar[{{ $i }}][desc]" class="form-control form-control-sm" value="{{ $g[2] ?? '' }}" placeholder="Descripción">
                    <button type="button" class="btn btn-sm btn-outline-danger rep-del" title="Quitar">&times;</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repGar"><i class="bi bi-plus-lg"></i> Agregar garantía</button>
        </div>

        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-2">🪜 Pasos del proceso <span class="text-secondary">(el número se pone solo)</span></label>
            <div id="repStep">
                @foreach($stepRows as $i => $s)
                <div class="rep-row d-flex gap-2 mb-2">
                    <input type="text" name="steps[{{ $i }}][emoji]" class="form-control form-control-sm text-center" style="max-width:56px" value="{{ $s[0] ?? '' }}" placeholder="🎨">
                    <input type="text" name="steps[{{ $i }}][title]" class="form-control form-control-sm" style="max-width:190px" value="{{ $s[1] ?? '' }}" placeholder="Título">
                    <input type="text" name="steps[{{ $i }}][desc]" class="form-control form-control-sm" value="{{ $s[2] ?? '' }}" placeholder="Descripción">
                    <button type="button" class="btn btn-sm btn-outline-danger rep-del" title="Quitar">&times;</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repStep"><i class="bi bi-plus-lg"></i> Agregar paso</button>
        </div>

        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-2">🔢 Números <span class="text-secondary">(contadores animados)</span></label>
            <div id="repNum">
                @foreach($numRows as $i => $n)
                <div class="rep-row d-flex gap-2 mb-2">
                    <input type="text" name="numbers[{{ $i }}][value]" class="form-control form-control-sm" style="max-width:100px" value="{{ $n[0] ?? '' }}" placeholder="500">
                    <input type="text" name="numbers[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $n[1] ?? '' }}" placeholder="Promociones felices">
                    <button type="button" class="btn btn-sm btn-outline-danger rep-del" title="Quitar">&times;</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repNum"><i class="bi bi-plus-lg"></i> Agregar número</button>
        </div>

        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-2">🏫 Colegios de la franja "Ya visten Glauren"</label>
            <input type="text" name="trusted_label" class="form-control form-control-sm mb-2" value="{{ $values['home_trusted_label'] ?? 'Colegios que ya visten Glauren' }}" placeholder="Etiqueta (ej. Colegios que ya visten Glauren)">
            <div id="repCol">
                @foreach($colList as $i => $c)
                <div class="rep-row d-flex gap-2 mb-2">
                    <input type="text" name="colegio[{{ $i }}]" class="form-control form-control-sm" value="{{ $c }}" placeholder="Nombre del colegio">
                    <button type="button" class="btn btn-sm btn-outline-danger rep-del" title="Quitar">&times;</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-light rep-add" data-rep="repCol"><i class="bi bi-plus-lg"></i> Agregar colegio</button>
        </div>

        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label small d-block mb-1">📝 Bloque de texto SEO (final del home) <span class="text-secondary">— acepta HTML</span></label>
            <textarea name="home_seo_content" class="form-control" rows="8" style="font-family:monospace;font-size:12px">{{ $values['home_seo_content'] ?? '' }}</textarea>
        </div>

        @foreach($fields as $key => [$label, $type])
            <div class="mb-3">
                <label class="form-label small text-secondary">{{ $label }}</label>
                @if($type === 'textarea')
                    <textarea name="settings[{{ $key }}]" class="form-control" rows="3">{{ $values[$key] ?? '' }}</textarea>
                @else
                    <input type="text" name="settings[{{ $key }}]" class="form-control" value="{{ $values[$key] ?? '' }}">
                @endif
            </div>
        @endforeach
        <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar textos</button>
    </form>
</div>

<script>
(function(){
    var c = 5000;
    function bindDel(row){
        var d = row.querySelector('.rep-del');
        if(d) d.onclick = function(){
            var rep = row.parentNode;
            if(rep.querySelectorAll('.rep-row').length > 1) row.remove();
            else row.querySelectorAll('input,textarea').forEach(function(i){ i.value=''; });
        };
    }
    document.querySelectorAll('.rep-row').forEach(bindDel);
    document.querySelectorAll('.rep-add').forEach(function(btn){
        btn.addEventListener('click', function(){
            var rep = document.getElementById(btn.dataset.rep);
            var rows = rep.querySelectorAll('.rep-row');
            var clone = rows[rows.length-1].cloneNode(true);
            var idx = ++c;
            clone.querySelectorAll('input,textarea,select').forEach(function(inp){
                inp.value = '';
                inp.name = inp.name.replace(/\[\d+\]/, '['+idx+']');
            });
            rep.appendChild(clone); bindDel(clone);
            var f = clone.querySelector('input'); if(f) f.focus();
        });
    });
})();
</script>
@endsection

@extends('layouts.app')
@section('title', 'Diseñar '.$type->name)

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Alfa+Slab+One&family=Anton&family=Archivo+Black&family=Bangers&family=Bebas+Neue&family=Black+Ops+One&family=Bungee&family=Caveat:wght@700&family=Fjalla+One&family=Graduate&family=Great+Vibes&family=Lobster&family=Luckiest+Guy&family=Monoton&family=Montserrat:wght@800&family=Oswald:wght@700&family=Pacifico&family=Passion+One:wght@700&family=Permanent+Marker&family=Righteous&family=Rye&family=Sacramento&family=Shrikhand&family=Special+Elite&family=Staatliches&family=Teko:wght@700&family=Titan+One&display=swap" rel="stylesheet">
<style>
    html,body{ height:100%; overflow:hidden; }
    #wrap{ display:flex; height:100vh; }
    #viewer{ flex:1; position:relative; min-width:0; }
    #viewer canvas{ display:block; }
    #panel{ width:360px; background:var(--panel); border-left:1px solid var(--bd); overflow-y:auto; padding:20px; }
    #panel::-webkit-scrollbar{ width:8px; } #panel::-webkit-scrollbar-thumb{ background:#2c313c; border-radius:8px; }
    .lbl{ font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#98a0ac; font-weight:600; }
    /* Secciones plegables del panel */
    .sect{ border:1px solid var(--bd); border-radius:10px; margin-bottom:8px; background:var(--panel2); overflow:hidden; }
    .sect>summary{ list-style:none; cursor:pointer; padding:11px 13px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#cfd3da; display:flex; align-items:center; gap:8px; user-select:none; }
    .sect>summary::-webkit-details-marker{ display:none; }
    .sect>summary::after{ content:'▸'; margin-left:auto; color:var(--g-gold,#c9a54b); transition:transform .15s; }
    .sect[open]>summary::after{ transform:rotate(90deg); }
    .sect>summary:hover{ color:#fff; }
    .sect .sect-body{ padding:2px 13px 13px; }
    .part{ display:flex; align-items:center; gap:10px; background:var(--panel2); border:1px solid var(--bd); border-radius:10px; padding:8px 10px; margin-bottom:6px; }
    .part .nm{ flex:1; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .part input[type=color]{ width:40px; height:30px; border:1px solid var(--bd); border-radius:6px; background:var(--panel); cursor:pointer; padding:2px; }
    .swatches{ display:flex; gap:5px; flex-wrap:wrap; margin:-2px 0 10px; }
    .sw{ width:20px; height:20px; border-radius:5px; cursor:pointer; border:2px solid transparent; }
    .sw:hover{ transform:scale(1.15); }
    .price-box{ background:var(--panel2); border:1px solid var(--bd); border-radius:10px; padding:14px; }
    .price-box .total{ font-size:26px; font-weight:800; }
    #hint{ position:absolute; bottom:14px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,.45); color:#98a0ac; padding:7px 14px; border-radius:999px; font-size:12.5px; pointer-events:none; }
    /* Aviso grande sobre la chaqueta para colocar/mover */
    #placeBanner{ position:absolute; top:16px; left:50%; transform:translateX(-50%); display:none; align-items:center; gap:9px;
        background:linear-gradient(120deg,#f6cf5b,#e9b83f); color:#1a1405; font-weight:800; font-size:14.5px;
        padding:11px 20px; border-radius:999px; box-shadow:0 6px 24px rgba(233,184,63,.45); pointer-events:none; z-index:5; max-width:88%; text-align:center; }
    #placeBanner.show{ display:inline-flex; animation:pbPulse 1.4s ease-in-out infinite; }
    #placeBanner.move{ background:#1c2a1c; color:#bff5d0; box-shadow:0 6px 20px rgba(0,0,0,.4); animation:none; }
    @keyframes pbPulse{ 0%,100%{ transform:translateX(-50%) scale(1); } 50%{ transform:translateX(-50%) scale(1.04); } }
    @media(prefers-reduced-motion:reduce){ #placeBanner.show{ animation:none; } }
    #spin{ position:absolute; inset:0; display:none; align-items:center; justify-content:center; flex-direction:column; gap:12px; background:rgba(14,16,20,.75); color:#98a0ac; z-index:5; }
    #spin.on{ display:flex; }
    .loader{ width:40px; height:40px; border:4px solid var(--panel2); border-top-color:#4f8cff; border-radius:50%; animation:sp 1s linear infinite; }
    @keyframes sp{ to{ transform:rotate(360deg); } }
    .patch-item{ display:flex; align-items:center; gap:8px; font-size:12px; color:#98a0ac; background:var(--panel2); border:1px solid var(--bd); border-radius:8px; padding:6px 9px; margin-top:6px; }
    .patch-item button{ margin-left:auto; }
    /* Compuerta de leads */
    #leadGate{ position:fixed; inset:0; z-index:200; display:flex; align-items:center; justify-content:center; padding:24px;
        background:rgba(8,11,20,.86); backdrop-filter:blur(8px); }
    #leadGate.hidden{ display:none; }
    .lg-card{ width:100%; max-width:420px; background:var(--g-surface,#14171e); border:1px solid var(--g-line,#2a2f3a); border-radius:16px; padding:32px; }
    .lg-eyebrow{ font-family:var(--g-font-label,'Space Grotesk',sans-serif); text-transform:uppercase; letter-spacing:.2em; font-size:11px; color:var(--g-gold,#c9a54b); }
    .lg-card h2{ font-family:var(--g-font-display,'Anton',sans-serif); text-transform:uppercase; font-size:30px; margin:8px 0 6px; }
    .lg-card p{ color:var(--g-muted,#8b93a7); font-size:14px; margin-bottom:20px; }
    .lg-field{ margin-bottom:12px; }
    .lg-field label{ display:block; font-size:12px; color:var(--g-muted,#8b93a7); margin-bottom:5px; }
    .lg-field input{ width:100%; background:#1e222b; border:1px solid var(--g-line,#2a2f3a); color:#e8eaed; border-radius:8px; padding:12px; font-size:15px; }
    .lg-field input:focus{ outline:none; border-color:var(--g-gold,#c9a54b); }
    .lg-check{ display:flex; align-items:center; gap:9px; font-size:13px; color:#c7ccd4; margin:8px 0 18px; }
    .lg-check a{ color:var(--g-gold,#c9a54b); }
    .lg-btn{ width:100%; background:var(--g-gold,#c9a54b); color:#1a1405; border:none; border-radius:10px; padding:14px; font-weight:700; font-size:15px; cursor:pointer; }
    .lg-btn:hover{ filter:brightness(1.08); }
    .lg-err{ background:#3a1f26; color:#ffb4bd; border-radius:8px; padding:9px 12px; font-size:13px; margin-bottom:14px; }
    .lg-back{ display:block; text-align:center; margin-top:14px; color:var(--g-muted,#8b93a7); font-size:13px; text-decoration:none; }
    @media (max-width:820px){ #wrap{ flex-direction:column; } #panel{ width:100%; border-left:none; border-top:1px solid var(--bd); max-height:48vh; } }
</style>
@endpush

@section('content')

{{-- Compuerta: datos del cliente antes de diseñar --}}
<div id="leadGate">
    <div class="lg-card">
        <div class="lg-eyebrow">{{ $type->icon }} {{ $type->name }}</div>
        <h2>Antes de diseñar</h2>
        <p>Déjanos tus datos para guardar tu diseño y enviarte tu cotización.</p>
        <div id="lgError" class="lg-err d-none"></div>
        <div class="lg-field"><label>Nombre *</label><input type="text" id="lgName" placeholder="Tu nombre"></div>
        <div class="lg-field"><label>Correo *</label><input type="email" id="lgEmail" placeholder="tucorreo@ejemplo.com"></div>
        <div class="lg-field"><label>WhatsApp (opcional)</label><input type="text" id="lgPhone" placeholder="3001234567"></div>
        <label class="lg-check"><input type="checkbox" id="lgTerms"> Acepto los <a href="{{ route('page.show', 'terminos-y-servicios') }}" target="_blank">términos y servicios</a></label>
        <button class="lg-btn" id="lgSubmit">Empezar a diseñar</button>
        <a href="{{ route('home') }}" class="lg-back">← Volver</a>
    </div>
</div>

<div id="wrap">
    <div id="viewer">
        <div id="spin"><div class="loader"></div><span id="spinTxt">Cargando modelo 3D…</span></div>
        <div id="placeBanner"><span id="placeBannerTxt"></span></div>
        <div id="hint"><i class="bi bi-mouse"></i> Arrastra para girar · Rueda para zoom · Clic para poner parche</div>
    </div>

    <aside id="panel">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="fw-bold fs-5">{{ $type->icon }} {{ $type->name }}</div>
                <a href="{{ route('personalizar') }}" class="small text-secondary text-decoration-none"><i class="bi bi-arrow-left"></i> Elegir otro tipo</a>
            </div>
        </div>

        @unless($type->hasModel())
            <div class="alert alert-warning small">Este tipo aún no tiene modelo 3D cargado. El administrador debe subirlo desde el panel.</div>
        @endunless

        {{-- Selector de modelo (solo tipos paramétricos; se oculta para modelos .glb editables) --}}
        <div class="mb-2" id="viewSegWrap">
            <div class="lbl mb-2">Modelo</div>
            <div class="btn-group btn-group-sm w-100" id="viewSeg" role="group">
                <button type="button" class="btn btn-primary" data-view="zonas">Editar por zonas</button>
                <button type="button" class="btn btn-outline-light" data-view="real" {{ $type->hasModel() ? '' : 'disabled' }}>👕 Modelo realista</button>
            </div>
            @if($type->hasModel())
                <div class="small mt-2" style="color:var(--g-gold,#c9a54b)"><i class="bi bi-info-circle"></i> Pulsa <b>«👕 Modelo realista»</b> para ver el 3D cargado.</div>
            @endif
        </div>

        <details class="sect" open>
            <summary>🎨 Colores de las piezas</summary>
            <div class="sect-body">
                <div id="parts"><div class="small text-secondary">Cargando piezas…</div></div>
            </div>
        </details>

        <details class="sect d-none" id="stripeBox" open>
            <summary>➖ Rayas de la manga</summary>
            <div class="sect-body">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="stripeOn" checked>
                    <label class="form-check-label small" for="stripeOn">Mostrar rayas</label>
                </div>
                <div id="stripeOpts">
                    <label class="small text-secondary">Elige qué rayas mostrar</label>
                    <div id="stripeList" class="mb-2"></div>
                    <div class="small text-secondary mt-1">Tip: el color de cada raya se cambia en «Colores de las piezas».</div>
                </div>
            </div>
        </details>

        <details class="sect" id="ribBox">
            <summary>➖ Rayas tejidas (pretina, puños, cuello)</summary>
            <div class="sect-body">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" id="ribStripes">
                    <label class="form-check-label small" for="ribStripes">Activar rayas tejidas</label>
                </div>
                <div id="ribColors" class="d-none d-flex align-items-center gap-2 small text-secondary">
                    Colores:
                    <input type="color" id="ribColorA" value="#e0a422" title="Raya 1" style="width:34px;height:26px;border:1px solid var(--bd);border-radius:5px;background:var(--panel);padding:1px">
                    <input type="color" id="ribColorB" value="#101319" title="Raya 2" style="width:34px;height:26px;border:1px solid var(--bd);border-radius:5px;background:var(--panel);padding:1px">
                </div>
            </div>
        </details>

        <details class="sect">
            <summary>🖼️ Imágenes y logos</summary>
            <div class="sect-body">
                <div class="small text-secondary mb-2" style="line-height:1.5">
                    <b>1.</b> Sube tu logo (PNG con fondo transparente) &nbsp;<b>2.</b> Haz clic sobre la chaqueta para pegarlo &nbsp;<b>3.</b> Arrástralo para moverlo
                </div>
                <button class="btn btn-outline-light btn-sm w-100 mb-2" id="imgBtn"><i class="bi bi-image"></i> Subir imagen (PNG)</button>
                <img id="patchPreview" class="d-none" alt="vista previa" style="max-width:100%;max-height:90px;object-fit:contain;border:1px solid var(--bd);border-radius:8px;margin-bottom:8px;background:#fff;padding:4px">
            </div>
        </details>

        <details class="sect">
            <summary>🔤 Texto</summary>
            <div class="sect-body">
            <div class="small text-secondary mb-2" style="line-height:1.5">
                <b>1.</b> Escribe el texto y la fuente &nbsp;<b>2.</b> Pulsa «Crear texto» &nbsp;<b>3.</b> Haz clic sobre la chaqueta para pegarlo &nbsp;<b>4.</b> Arrástralo para moverlo.
                <br><span style="opacity:.85">¿Varios (nombre, curso, iniciales…)? Repite: crea otro y haz clic en otro lugar.</span>
            </div>
            <div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control" id="patchText" placeholder="Escribe aquí (ej: Valentina)" maxlength="28">
                <input type="color" class="form-control form-control-color" id="patchTextColor" value="#ffffff" title="Color">
            </div>
            <div class="input-group input-group-sm mb-2">
                <select class="form-select" id="patchFont" title="Fuente">
                    <optgroup label="Deportivas / Universitarias">
                        <option value="Anton" data-weight="400" style="font-family:'Anton'">Anton (varsity)</option>
                        <option value="Graduate" data-weight="400" style="font-family:'Graduate'">Graduate (college)</option>
                        <option value="Bebas Neue" data-weight="400" style="font-family:'Bebas Neue'">Bebas Neue (alta)</option>
                        <option value="Oswald" data-weight="700" style="font-family:'Oswald'">Oswald (moderna)</option>
                        <option value="Teko" data-weight="700" style="font-family:'Teko'">Teko (condensada)</option>
                        <option value="Fjalla One" data-weight="400" style="font-family:'Fjalla One'">Fjalla One</option>
                        <option value="Staatliches" data-weight="400" style="font-family:'Staatliches'">Staatliches</option>
                    </optgroup>
                    <optgroup label="Fuertes / Display">
                        <option value="Archivo Black" data-weight="400" style="font-family:'Archivo Black'">Archivo Black</option>
                        <option value="Montserrat" data-weight="800" style="font-family:'Montserrat'">Montserrat (limpia)</option>
                        <option value="Alfa Slab One" data-weight="400" style="font-family:'Alfa Slab One'">Alfa Slab One</option>
                        <option value="Titan One" data-weight="400" style="font-family:'Titan One'">Titan One</option>
                        <option value="Passion One" data-weight="700" style="font-family:'Passion One'">Passion One</option>
                        <option value="Righteous" data-weight="400" style="font-family:'Righteous'">Righteous</option>
                        <option value="Abril Fatface" data-weight="400" style="font-family:'Abril Fatface'">Abril Fatface</option>
                        <option value="Shrikhand" data-weight="400" style="font-family:'Shrikhand'">Shrikhand</option>
                    </optgroup>
                    <optgroup label="Urbanas / Divertidas">
                        <option value="Bungee" data-weight="400" style="font-family:'Bungee'">Bungee (urbana)</option>
                        <option value="Bangers" data-weight="400" style="font-family:'Bangers'">Bangers (cómic)</option>
                        <option value="Luckiest Guy" data-weight="400" style="font-family:'Luckiest Guy'">Luckiest Guy</option>
                        <option value="Black Ops One" data-weight="400" style="font-family:'Black Ops One'">Black Ops One</option>
                        <option value="Monoton" data-weight="400" style="font-family:'Monoton'">Monoton (retro)</option>
                    </optgroup>
                    <optgroup label="Script / A mano">
                        <option value="Permanent Marker" data-weight="400" style="font-family:'Permanent Marker'">Permanent Marker</option>
                        <option value="Pacifico" data-weight="400" style="font-family:'Pacifico'">Pacifico (script)</option>
                        <option value="Lobster" data-weight="400" style="font-family:'Lobster'">Lobster (cursiva)</option>
                        <option value="Caveat" data-weight="700" style="font-family:'Caveat'">Caveat (a mano)</option>
                        <option value="Great Vibes" data-weight="400" style="font-family:'Great Vibes'">Great Vibes (elegante)</option>
                        <option value="Sacramento" data-weight="400" style="font-family:'Sacramento'">Sacramento (fina)</option>
                        <option value="Special Elite" data-weight="400" style="font-family:'Special Elite'">Special Elite (máquina)</option>
                        <option value="Rye" data-weight="400" style="font-family:'Rye'">Rye (western)</option>
                    </optgroup>
                </select>
                <button class="btn btn-outline-light" id="textBtn">Crear texto</button>
            </div>
            <div class="input-group input-group-sm mb-2">
                <span class="input-group-text">Borde</span>
                <input type="color" class="form-control form-control-color" id="patchOutline" value="#000000" title="Color del borde">
                <span class="input-group-text bg-transparent">
                    <input class="form-check-input mt-0 me-1" type="checkbox" id="patchOutlineOn" checked title="Activar borde">
                    <label for="patchOutlineOn" class="small mb-0">Con borde</label>
                </span>
            </div>
            <label class="small text-secondary">Curvar texto (arco) <span id="curveVal" class="text-secondary">recto</span></label>
            <input type="range" class="form-range" id="patchCurve" min="-100" max="100" step="5" value="0">
            </div>
        </details>

        <details class="sect">
            <summary>🎯 Ajustar lo colocado</summary>
            <div class="sect-body">
                <div id="patchStatus" class="small text-secondary mb-2">Haz clic sobre la prenda para colocar. <b>Arrastra</b> un texto o imagen para moverlo.</div>
                <div class="small mb-2" id="selInfo" style="color:var(--g-gold,#c9a54b)"><i class="bi bi-cursor"></i> Ningún elemento seleccionado</div>
                <label class="small text-secondary">Tamaño</label>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <button type="button" class="btn btn-outline-light btn-sm px-3 fw-bold" id="sizeMinus" title="Más pequeño">−</button>
                    <input type="range" class="form-range flex-grow-1" id="patchSize" min="0.05" max="2" step="0.02" value="0.4">
                    <button type="button" class="btn btn-outline-light btn-sm px-3 fw-bold" id="sizePlus" title="Más grande">+</button>
                </div>
                <label class="small text-secondary">Girar</label>
                <input type="range" class="form-range" id="patchRoll" min="0" max="360" step="5" value="0">
                <div id="patchList"></div>
                <button class="btn btn-outline-secondary btn-sm w-100 mt-2" id="undoPatch"><i class="bi bi-arrow-counterclockwise"></i> Borrar último</button>
            </div>
        </details>

        <details class="sect">
            <summary>🛒 Cantidad y cotización</summary>
            <div class="sect-body">
                <label class="small text-secondary">Cantidad</label>
                <input type="number" class="form-control form-control-sm mb-2" id="qty" min="1" value="1">
                {{-- Precio interno (no se muestra al cliente ni se envía por WhatsApp) --}}
                <span id="priceTotal" class="d-none"></span><span id="priceDetail" class="d-none"></span>
                <button class="btn btn-success w-100" id="quoteBtn" data-bs-toggle="modal" data-bs-target="#quoteModal">
                    <i class="bi bi-whatsapp"></i> Pedir cotización
                </button>
            </div>
        </details>

        <details class="sect">
            <summary>⚙️ Fondo y vista</summary>
            <div class="sect-body">
            @php $wm = \App\Modules\Chaquetas\Models\Setting::get('designer_watermark'); @endphp
            <div class="btn-group btn-group-sm w-100 mb-2" role="group" id="bgSeg">
                @if($wm)
                    <button class="btn btn-primary" data-bg="glauren"><i class="bi bi-droplet"></i> Fondo Glauren</button>
                @else
                    <button class="btn btn-primary" data-bg="studio">Estudio</button>
                    <button class="btn btn-outline-light" data-bg="white">Blanco</button>
                    <button class="btn btn-outline-light" data-bg="dark">Oscuro</button>
                @endif
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="rotToggle" checked>
                <label class="form-check-label small" for="rotToggle">Girar automático</label>
            </div>
            </div>
        </details>
    </aside>
</div>

<input type="file" id="imgInput" accept="image/*" style="display:none">

{{-- Modal de datos del cliente --}}
<div class="modal fade" id="quoteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--panel); color:#e8eaed; border:1px solid var(--bd);">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="bi bi-whatsapp text-success"></i> Tus datos para la cotización</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="quoteError" class="alert alert-danger py-2 small d-none"></div>
        <div class="mb-2"><label class="small text-secondary">Nombre *</label><input type="text" class="form-control" id="cName"></div>
        <div class="mb-2"><label class="small text-secondary">WhatsApp / teléfono *</label><input type="text" class="form-control" id="cPhone" placeholder="3001234567"></div>
        <div class="mb-2"><label class="small text-secondary">Colegio / promoción</label><input type="text" class="form-control" id="cSchool"></div>
        <div class="mb-2"><label class="small text-secondary">Correo (opcional)</label><input type="email" class="form-control" id="cEmail"></div>
        <p class="small text-secondary mb-0">Al enviar, se abrirá WhatsApp con tu diseño y guardaremos tu solicitud.</p>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="sendQuote"><i class="bi bi-send"></i> Enviar cotización</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@php
    // Marca de agua: usar ruta relativa (mismo origen) para evitar bloqueo CORS localhost/127.0.0.1
    $wmRel = \App\Modules\Chaquetas\Models\Setting::get('designer_watermark');
    if ($wmRel) { $p = strpos($wmRel, '/storage/'); if ($p !== false) { $wmRel = substr($wmRel, $p); } }
@endphp
<script>
    window.CHAQUETA = {
        typeId:     {{ $type->id }},
        name:       @json($type->name),
        basePrice:  {{ (float) $type->base_price }},
        patchPrice: {{ (float) $type->patch_price }},
        modelUrl:   @json($type->model_path ? '/storage/'.$type->model_path : null),
        defaultView:@json($type->default_view ?? 'parametrico'),
        watermark:  @json($wmRel),
        quoteUrl:   @json(route('quote.store')),
        leadUrl:    @json(route('lead.store')),
        typeName:   @json($type->name),
        csrf:       @json(csrf_token()),
    };
</script>
<script>
(function(){
    const KEY='glauren_lead';
    const gate=document.getElementById('leadGate');
    function prefill(d){ const n=document.getElementById('cName'), e=document.getElementById('cEmail'), p=document.getElementById('cPhone');
        if(n&&!n.value) n.value=d.name||''; if(e&&!e.value) e.value=d.email||''; if(p&&!p.value&&d.phone) p.value=d.phone; }
    const saved=JSON.parse(localStorage.getItem(KEY)||'null');
    if(saved&&saved.email){ gate.classList.add('hidden'); prefill(saved); }
    document.getElementById('lgSubmit').addEventListener('click', async ()=>{
        const name=document.getElementById('lgName').value.trim(), email=document.getElementById('lgEmail').value.trim(),
              phone=document.getElementById('lgPhone').value.trim(), terms=document.getElementById('lgTerms').checked, err=document.getElementById('lgError');
        if(!name||!email){ err.textContent='Escribe tu nombre y correo.'; err.classList.remove('d-none'); return; }
        if(!/.+@.+\..+/.test(email)){ err.textContent='Correo no válido.'; err.classList.remove('d-none'); return; }
        if(!terms){ err.textContent='Debes aceptar los términos y servicios.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        const btn=document.getElementById('lgSubmit'); btn.disabled=true; btn.textContent='Un momento…';
        try{ await fetch(window.CHAQUETA.leadUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.CHAQUETA.csrf,'Accept':'application/json'},
            body:JSON.stringify({name,email,phone,jacket_type:window.CHAQUETA.typeName,accepted_terms:true})}); }catch(e){}
        localStorage.setItem(KEY, JSON.stringify({name,email,phone})); prefill({name,email,phone}); gate.classList.add('hidden');
    });
})();
</script>
<script type="importmap">
{ "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
}}
</script>
<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
    import { KTX2Loader } from 'three/addons/loaders/KTX2Loader.js';
    import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';
    import { DecalGeometry } from 'three/addons/geometries/DecalGeometry.js';

    const CFG = window.CHAQUETA;
    const $ = id => document.getElementById(id);
    const viewer = $('viewer');
    const money = n => '$'+ (Math.round(n)||0).toLocaleString('es-CO');

    /* Escena */
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(42, viewer.clientWidth/viewer.clientHeight, 0.01, 1000);
    camera.position.set(0,0.4,6);
    const renderer = new THREE.WebGLRenderer({ antialias:true, preserveDrawingBuffer:true, alpha:true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio,2));
    renderer.setSize(viewer.clientWidth, viewer.clientHeight);
    renderer.toneMapping = THREE.ACESFilmicToneMapping; renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    viewer.appendChild(renderer.domElement);

    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

    function grad(a,b){ const c=document.createElement('canvas'); c.width=c.height=512; const x=c.getContext('2d');
        const g=x.createRadialGradient(256,190,50,256,320,470); g.addColorStop(0,a); g.addColorStop(1,b);
        x.fillStyle=g; x.fillRect(0,0,512,512); const t=new THREE.CanvasTexture(c); t.colorSpace=THREE.SRGBColorSpace; return t; }
    const backgrounds = { studio:grad('#2c313d','#0c0e12'), white:grad('#ffffff','#d6dae0'), dark:new THREE.Color(0x0c0e12) };
    scene.background = backgrounds.studio;

    // Marca de agua Glauren como fondo (administrable). Se pone por defecto cuando existe.
    if(CFG.watermark){
        new THREE.TextureLoader().load(CFG.watermark, tex=>{
            tex.colorSpace=THREE.SRGBColorSpace; backgrounds.glauren=tex; scene.background=tex;
            const gb=document.querySelector('#bgSeg button[data-bg="glauren"]');
            if(gb){ document.querySelectorAll('#bgSeg button').forEach(x=>{ x.classList.remove('btn-primary'); x.classList.add('btn-outline-light'); });
                gb.classList.add('btn-primary'); gb.classList.remove('btn-outline-light'); }
        });
    }

    scene.add(new THREE.HemisphereLight(0xffffff,0x30343d,0.5));
    const key=new THREE.DirectionalLight(0xffffff,2.6); key.position.set(4,8,6); key.castShadow=true;
    key.shadow.mapSize.set(2048,2048); key.shadow.camera.far=40; key.shadow.bias=-0.0004; scene.add(key);
    const fill=new THREE.DirectionalLight(0xcfe0ff,0.5); fill.position.set(-6,3,-4); scene.add(fill);
    const rim=new THREE.DirectionalLight(0xffffff,0.9); rim.position.set(0,4,-7); scene.add(rim);
    const floor=new THREE.Mesh(new THREE.CircleGeometry(20,64), new THREE.ShadowMaterial({opacity:0.3}));
    floor.rotation.x=-Math.PI/2; floor.receiveShadow=true; scene.add(floor);

    const controls=new OrbitControls(camera, renderer.domElement);
    controls.enableDamping=true; controls.dampingFactor=0.08; controls.autoRotate=true; controls.autoRotateSpeed=1.0;
    renderer.domElement.addEventListener('pointerdown', ()=>{ controls.autoRotate=false; $('rotToggle').checked=false; });

    const draco=new DRACOLoader(); draco.setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/');
    const ktx2=new KTX2Loader().setTranscoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/basis/').detectSupport(renderer);
    const loader=new GLTFLoader(); loader.setDRACOLoader(draco); loader.setKTX2Loader(ktx2); loader.setMeshoptDecoder(MeshoptDecoder);

    let current=null, modelSize=new THREE.Vector3(1,1,1), activeParts=[];
    const modelMeshes=[];
    const ribMats=[];
    // Rayas de la manga (franjas) detectadas en el modelo
    const stripeMeshes=[];   // {mesh, axis, center, orig:Float32Array, line:1|2}
    const palette=['#1b2a4a','#101319','#7a1f2b','#0d4d3c','#efe9dd','#b71c2b','#e0a422','#2f6fed','#d9dde2','#111111','#ffffff','#6b7280'];

    function normalizeModel(obj){
        let box=new THREE.Box3().setFromObject(obj); const size=box.getSize(new THREE.Vector3());
        const maxDim=Math.max(size.x,size.y,size.z)||1; obj.scale.setScalar(3.4/maxDim); obj.updateMatrixWorld(true);
        box=new THREE.Box3().setFromObject(obj); const c=box.getCenter(new THREE.Vector3()); obj.position.sub(c); obj.updateMatrixWorld(true);
        box=new THREE.Box3().setFromObject(obj); box.getSize(modelSize); floor.position.y=-modelSize.y/2-0.02;
        modelMeshes.length=0; obj.traverse(o=>{ if(o.isMesh) modelMeshes.push(o); });
    }
    function frameCamera(){ const s=modelSize, m=Math.max(s.x,s.y,s.z)||1; const d=(s.y/2)/Math.tan(camera.fov*Math.PI/360);
        camera.position.set(d*0.5, s.y*0.1, d*1.25+m*0.2); controls.target.set(0,0,0); controls.minDistance=m*0.35; controls.maxDistance=m*4; controls.update(); }

    function buildPartsUI(root){
        const seen=new Map();
        root.traverse(o=>{ if(o.isMesh){ o.castShadow=o.receiveShadow=true; const a=Array.isArray(o.material)?o.material:[o.material];
            a.forEach(m=>{ if(m && !seen.has(m.uuid)) seen.set(m.uuid,{mat:m,label:m.name||o.name||'Material'}); }); } });
        activeParts=Array.from(seen.values());
        const cont=$('parts'); cont.innerHTML='';
        if(!seen.size){ cont.innerHTML='<div class="small text-secondary">El modelo no expone materiales editables.</div>'; return; }
        let i=0;
        for(const {mat,label} of seen.values()){ i++;
            const row=document.createElement('div'); row.className='part';
            row.innerHTML=`<span class="nm" title="${label}">${label||('Parte '+i)}</span>`;
            const inp=document.createElement('input'); inp.type='color'; inp.value='#'+(mat.color?mat.color.getHexString():'cccccc');
            inp.addEventListener('input',e=>{ if(mat.color){ mat.color.set(e.target.value); mat.needsUpdate=true; } });
            row.appendChild(inp); cont.appendChild(row);
            const sw=document.createElement('div'); sw.className='swatches';
            palette.forEach(h=>{ const s=document.createElement('div'); s.className='sw'; s.style.background=h;
                s.addEventListener('click',()=>{ if(mat.color){ mat.color.set(h); mat.needsUpdate=true; inp.value=h; } }); sw.appendChild(s); });
            cont.appendChild(sw);
        }
    }

    function loadModel(url){
        $('spin').classList.add('on'); $('spinTxt').textContent='Cargando modelo 3D…';
        fetch(url).then(r=>{ if(!r.ok) throw new Error('HTTP '+r.status); return r.arrayBuffer(); })
        .then(buf=> loader.parse(buf,'',(gltf)=>{
            if(current) scene.remove(current); clearDecals();
            current=gltf.scene; scene.add(current); normalizeModel(current); frameCamera(); buildPartsUI(current);
            $('ribBox').style.display='none'; detectStripes(current);
            $('spin').classList.remove('on'); controls.autoRotate=$('rotToggle').checked; updatePrice();
        },(err)=>{ console.error(err); $('spin').classList.remove('on'); $('parts').innerHTML='<div class="text-danger small">No se pudo cargar el modelo.</div>'; }))
        .catch(e=>{ console.error(e); $('spin').classList.remove('on'); $('parts').innerHTML='<div class="text-danger small">No se pudo descargar el modelo.</div>'; });
    }

    /* ===== Rayas de la manga (franjas del modelo) ===== */
    function detectStripes(root){
        stripeMeshes.length=0;
        // Materiales de cada manga (para rellenar el hueco al quitar la raya)
        const sleeveMat={};
        root.traverse(o=>{ if(o.isMesh && !/franja|raya|stripe/i.test(o.name)){
            if(/manga_izquierda/i.test(o.name)) sleeveMat.Izq=o.material;
            if(/manga_derecha/i.test(o.name))   sleeveMat.Der=o.material;
        }});
        root.traverse(o=>{
            if(o.isMesh && /franja|raya|stripe|line/i.test(o.name)){
                const g=o.geometry; if(!g.attributes||!g.attributes.position) return;
                g.computeBoundingBox(); const bb=g.boundingBox; const size=new THREE.Vector3(); bb.getSize(size);
                const center=new THREE.Vector3(); bb.getCenter(center);
                // Eje más largo = a lo largo del brazo (se conserva). El ancho = los otros dos ejes.
                const dims=[size.x,size.y,size.z]; const long=dims.indexOf(Math.max(...dims));
                const orig=g.attributes.position.array.slice(0); // copia original
                const line=/_2_|franja_2|raya_2/i.test(o.name)?2:1;
                const side=/derecha/i.test(o.name)?'Der':'Izq';
                const label = 'Manga '+(side==='Der'?'derecha':'izquierda')+' · raya '+line;
                stripeMeshes.push({mesh:o, long, center:center.clone(), orig, line, side, label,
                    stripeMat:o.material, sleeveMat:sleeveMat[side]||null});
            }
        });
        // Orden: izquierda primero, luego por número de raya
        stripeMeshes.sort((a,b)=> a.side===b.side ? a.line-b.line : (a.side==='Izq'?-1:1));

        const has=stripeMeshes.length>0;
        $('stripeBox').classList.toggle('d-none', !has);
        if(has){ $('stripeOn').checked=true; buildStripeList(); applyStripes(); }
    }

    function buildStripeList(){
        const cont=$('stripeList'); cont.innerHTML='';
        stripeMeshes.forEach((s,idx)=>{
            const row=document.createElement('div'); row.className='form-check form-check-sm';
            row.innerHTML='<input class="form-check-input str-chk" type="checkbox" id="str_'+idx+'" data-idx="'+idx+'" checked>'
                +'<label class="form-check-label small" for="str_'+idx+'">'+s.label+'</label>';
            cont.appendChild(row);
        });
        cont.querySelectorAll('.str-chk').forEach(c=>c.addEventListener('change', applyStripes));
    }

    function applyStripes(){
        const master=$('stripeOn').checked;
        stripeMeshes.forEach((s,idx)=>{
            const chk=document.getElementById('str_'+idx);
            const indiv = chk ? chk.checked : true;
            // "Mostrada" = maestro encendido Y su casilla marcada; si no, se funde con la manga (rellena el hueco)
            const shown = master && indiv;
            s.mesh.visible = true;  // nunca se oculta: así no queda hueco
            s.mesh.material = shown ? s.stripeMat : (s.sleeveMat || s.stripeMat);
        });
    }

    /* Parches (decals) — colocar, seleccionar, mover y ajustar en vivo */
    const raycaster=new THREE.Raycaster(); const ndc=new THREE.Vector2();
    let pendingTex=null, pendingAspect=1, decalSize=0.4, decalRoll=0, selected=-1, pendingLabel='';
    const decals=[];   // {mesh, point, normal, roll, size, tex, aspect, obj}
    const setPatchStatus=t=>$('patchStatus').innerHTML=t;

    // Modo visual claro: "colocar" (aviso dorado + cursor cruz) o "mover" (aviso verde)
    let armed=false;
    function setMode(){
        const b=$('placeBanner'), t=$('placeBannerTxt'), cv=renderer.domElement;
        if(armed && pendingTex){
            b.className='show'; t.innerHTML='👆 Haz clic en la chaqueta para colocar '+(pendingLabel||'tu diseño');
            cv.style.cursor='crosshair';
        } else if(selected>=0){
            b.className='show move'; t.innerHTML='✋ Arrástralo para moverlo · Tamaño −/+ para cambiar el tamaño';
            cv.style.cursor='grab';
        } else {
            b.className=''; cv.style.cursor='';
        }
    }

    function clearDecals(){ decals.forEach(d=>{ scene.remove(d.obj); d.obj.geometry.dispose(); d.obj.material.dispose(); }); decals.length=0; selected=-1; if($('patchList')) $('patchList').innerHTML=''; updatePrice(); if(typeof setMode==='function') setMode(); }

    function makeTextTexture(text,color,family,weight,outline,curve){ family=family||'Anton'; weight=weight||400; curve=curve||0;
        const W=512,H=256; const c=document.createElement('canvas'); c.width=W;c.height=H; const x=c.getContext('2d');
        const ff=`"${family}", Arial, sans-serif`;
        x.clearRect(0,0,W,H); x.fillStyle=color; x.strokeStyle=outline||'#000'; x.lineJoin='round'; x.textAlign='center'; x.textBaseline='middle';
        const strength=Math.max(-1,Math.min(1,curve/100));
        // Tamaño de fuente que quepa (con arco, deja margen extra)
        let fs=150; x.font=`${weight} ${fs}px ${ff}`;
        const maxW = strength ? 430 : 470;
        while(x.measureText(text).width>maxW&&fs>24){ fs-=8; x.font=`${weight} ${fs}px ${ff}`; }
        const lw=fs*0.14;
        if(!strength){
            if(outline){ x.lineWidth=lw; x.strokeText(text,W/2,138); }
            x.fillText(text,W/2,138);
        } else {
            const dir = strength>0 ? 1 : -1;                 // + arco hacia arriba (rainbow)
            const chars=[...text];
            const widths=chars.map(ch=>x.measureText(ch).width);
            const totalW=widths.reduce((a,b)=>a+b,0);
            const angleTotal=Math.abs(strength)*1.9;         // apertura del arco
            const radius=Math.max(totalW/angleTotal, fs*0.9);
            // Centra el arco verticalmente: punto medio del arco → H/2
            const cy = H/2 + dir*radius*(1+Math.cos(angleTotal/2))/2;
            x.save(); x.translate(W/2, cy);
            let ang=-angleTotal/2;
            for(let i=0;i<chars.length;i++){
                ang += (widths[i]/2)/radius;
                x.save(); x.rotate(dir*ang); x.translate(0,-dir*radius);
                if(outline){ x.lineWidth=lw; x.strokeText(chars[i],0,0); }
                x.fillText(chars[i],0,0);
                x.restore();
                ang += (widths[i]/2)/radius;
            }
            x.restore();
        }
        const t=new THREE.CanvasTexture(c); t.colorSpace=THREE.SRGBColorSpace; t.anisotropy=8; return {tex:t, aspect:2, preview:c.toDataURL()}; }

    function loadDecalImage(file){ const url=URL.createObjectURL(file);
        new THREE.TextureLoader().load(url,tex=>{ tex.colorSpace=THREE.SRGBColorSpace; tex.anisotropy=8;
            pendingTex=tex; pendingAspect=(tex.image.width/tex.image.height)||1; pendingLabel='tu logo';
            const img=$('patchPreview'); img.src=url; img.classList.remove('d-none');
            armed=true; setMode();
            setPatchStatus('✅ Imagen lista. <b>Haz clic sobre la prenda</b> para colocarla; luego <b>arrástrala</b> para moverla o usa <b>Tamaño −/+</b>.');
        },undefined,()=>setPatchStatus('❌ No se pudo leer la imagen.')); }

    function buildGeo(mesh, point, normal, roll, size, aspect){
        const dummy=new THREE.Object3D(); dummy.position.copy(point); dummy.lookAt(point.clone().add(normal)); dummy.rotateZ(THREE.MathUtils.degToRad(roll));
        return new DecalGeometry(mesh, point, dummy.rotation, new THREE.Vector3(size, size/aspect, 0.45)); }
    function rebuild(i){ const d=decals[i]; const g=buildGeo(d.mesh,d.point,d.normal,d.roll,d.size,d.aspect); d.obj.geometry.dispose(); d.obj.geometry=g; }

    function addDecal(hit){ if(!pendingTex){ setPatchStatus('Primero sube una imagen o usa un texto ⬆️'); return; }
        const mesh=hit.object, point=hit.point.clone();
        const normal=hit.face.normal.clone().transformDirection(mesh.matrixWorld).normalize();
        const mat=new THREE.MeshStandardMaterial({ map:pendingTex, transparent:true, roughness:0.6, polygonOffset:true, polygonOffsetFactor:-6, depthWrite:false });
        const obj=new THREE.Mesh(buildGeo(mesh,point,normal,decalRoll,decalSize,pendingAspect), mat); obj.renderOrder=2; scene.add(obj);
        decals.push({mesh,point,normal,roll:decalRoll,size:decalSize,tex:pendingTex,aspect:pendingAspect,obj,label:pendingLabel});
        selectDecal(decals.length-1); updatePrice();
        armed=false; setMode();
        setPatchStatus('✅ Colocado. <b>Arrástralo</b> para moverlo, o usa <b>Tamaño −/+</b> para cambiar su tamaño.'); }

    function moveDecal(i, hit){ const d=decals[i]; d.mesh=hit.object; d.point=hit.point.clone();
        d.normal=hit.face.normal.clone().transformDirection(d.mesh.matrixWorld).normalize(); rebuild(i); }

    function selectDecal(i){ selected=i;
        if(i>=0){ $('patchSize').value=decals[i].size; $('patchRoll').value=decals[i].roll; decalSize=decals[i].size; decalRoll=decals[i].roll; }
        const info=$('selInfo'); if(info) info.innerHTML = i>=0 ? '<i class="bi bi-pencil-fill"></i> Editando: <b>'+(decals[i].label||('elemento '+(i+1)))+'</b>' : '<i class="bi bi-cursor"></i> Ningún elemento seleccionado';
        refreshDecalList(); if(i>=0){ armed=false; } setMode(); }
    function nudgeSize(delta){
        if(selected<0){ setPatchStatus('Primero <b>haz clic</b> en el texto o imagen que quieres cambiar.'); return; }
        const s=Math.min(2, Math.max(0.05, decals[selected].size+delta));
        decals[selected].size=s; decalSize=s; $('patchSize').value=s; rebuild(selected);
    }

    function refreshDecalList(){ const c=$('patchList'); c.innerHTML='';
        decals.forEach((d,i)=>{ const r=document.createElement('div'); r.className='patch-item'+(i===selected?' border-primary':'');
            const s=document.createElement('span'); s.style.cssText='flex:1;cursor:pointer'; s.textContent=(i===selected?'● ':'○ ')+(d.label || ('Parche '+(i+1)));
            s.onclick=()=>selectDecal(i); r.appendChild(s);
            const b=document.createElement('button'); b.className='btn btn-sm btn-outline-danger py-0'; b.textContent='Borrar';
            b.onclick=(ev)=>{ ev.stopPropagation(); scene.remove(d.obj); d.obj.geometry.dispose(); const k=decals.indexOf(d); if(k>-1) decals.splice(k,1); selected=-1; refreshDecalList(); updatePrice(); };
            r.appendChild(b); c.appendChild(r); }); }

    let downPos=null, dragging=-1;
    // Raycast: devuelve el punto sobre el modelo y, si lo hay, el índice del parche agarrado
    function pickAt(cx,cy){
        const rect=renderer.domElement.getBoundingClientRect();
        ndc.x=((cx-rect.left)/rect.width)*2-1; ndc.y=-((cy-rect.top)/rect.height)*2+1;
        raycaster.setFromCamera(ndc,camera);
        const hits=raycaster.intersectObjects(modelMeshes,true);
        if(!hits.length) return {hit:null, decal:-1};
        const p=hits[0].point; let best=-1, bestD=Infinity;
        decals.forEach((d,i)=>{ const dist=d.point.distanceTo(p); if(dist<d.size*0.6 && dist<bestD){ bestD=dist; best=i; } });
        return {hit:hits[0], decal:best};
    }
    renderer.domElement.addEventListener('pointerdown',e=>{
        downPos={x:e.clientX,y:e.clientY};
        if(!modelMeshes.length) return;
        const {decal}=pickAt(e.clientX,e.clientY);
        if(decal>=0){ dragging=decal; selectDecal(decal); controls.enabled=false;
            renderer.domElement.style.cursor='grabbing';
            setPatchStatus('✋ <b>Arrastra</b> para mover «'+(decals[decal].label||'elemento')+'». Suelta para fijarlo.'); }
    });
    window.addEventListener('pointermove',e=>{
        if(dragging<0) return; const {hit}=pickAt(e.clientX,e.clientY); if(hit) moveDecal(dragging, hit);
    });
    window.addEventListener('pointerup',e=>{
        const wasDrag=dragging>=0; dragging=-1; controls.enabled=true;
        if(!downPos) return; const moved=Math.hypot(e.clientX-downPos.x,e.clientY-downPos.y); downPos=null;
        if(wasDrag){ setMode(); setPatchStatus('✅ Movido. Con él seleccionado, usa <b>Tamaño −/+</b> para agrandarlo.'); return; }
        if(moved>6 || !modelMeshes.length) return;   // fue giro de cámara, no un clic
        const {hit,decal}=pickAt(e.clientX,e.clientY);
        if(decal>=0){ selectDecal(decal); setPatchStatus('Seleccionado «'+(decals[decal].label||'elemento')+'». Arrástralo o usa <b>Tamaño −/+</b>.'); return; }
        if(!hit){ setPatchStatus('Haz clic <b>sobre la prenda</b> (no en el fondo).'); return; }
        addDecal(hit);
    });

    $('imgBtn').addEventListener('click',()=>$('imgInput').click());
    $('imgInput').addEventListener('change',e=>{ if(e.target.files[0]) loadDecalImage(e.target.files[0]); });
    $('textBtn').addEventListener('click',()=>{ const t=$('patchText').value.trim(); if(!t){ setPatchStatus('Escribe un texto primero.'); return; }
        const sel=$('patchFont'), opt=sel.options[sel.selectedIndex];
        const family=sel.value, weight=opt.getAttribute('data-weight')||400;
        setPatchStatus('<span class="spinner-border spinner-border-sm"></span> Cargando fuente…');
        const outline = $('patchOutlineOn').checked ? $('patchOutline').value : null;
        const curve = parseInt($('patchCurve').value)||0;
        const render=()=>{ const r=makeTextTexture(t, $('patchTextColor').value, family, weight, outline, curve); pendingTex=r.tex; pendingAspect=r.aspect; pendingLabel='“'+t+'”';
            const img=$('patchPreview'); img.src=r.preview; img.classList.remove('d-none');
            armed=true; setMode();
            setPatchStatus('✅ Texto listo. <b>Haz clic sobre la chaqueta</b> para colocarlo. Repite para agregar más.'); };
        if(document.fonts && document.fonts.load){ document.fonts.load(`${weight} 40px "${family}"`).then(render).catch(render); } else { render(); }
    });
    $('patchCurve').addEventListener('input',e=>{ const v=parseInt(e.target.value)||0; $('curveVal').textContent = v===0?'recto':(v>0?'arco ↑ '+v+'%':'arco ↓ '+Math.abs(v)+'%'); });
    $('patchSize').addEventListener('input',e=>{ decalSize=parseFloat(e.target.value); if(selected>=0){ decals[selected].size=decalSize; rebuild(selected); } });
    $('sizeMinus').addEventListener('click',()=>nudgeSize(-0.08));
    $('sizePlus').addEventListener('click',()=>nudgeSize(0.08));
    $('patchRoll').addEventListener('input',e=>{ decalRoll=parseFloat(e.target.value); if(selected>=0){ decals[selected].roll=decalRoll; rebuild(selected); } });
    $('undoPatch').addEventListener('click',()=>{ const d=decals.pop(); if(d){ scene.remove(d.obj); d.obj.geometry.dispose(); selected=-1; refreshDecalList(); updatePrice(); setMode(); } });

    /* Precio */
    function qtyVal(){ return Math.max(1, parseInt($('qty').value)||1); }
    function unitPrice(){ return CFG.basePrice + decals.length*CFG.patchPrice; }
    function updatePrice(){ const per=unitPrice(), total=per*qtyVal();
        $('priceTotal').textContent=money(total); $('priceDetail').textContent=`${money(per)} c/u × ${qtyVal()} · incluye ${decals.length} parche(s)`; }
    $('qty').addEventListener('input', updatePrice);

    /* Vista */
    $('rotToggle').addEventListener('change',e=>controls.autoRotate=e.target.checked);
    $('bgSeg').addEventListener('click',e=>{ const b=e.target.closest('button'); if(!b) return;
        $('bgSeg').querySelectorAll('button').forEach(x=>{ x.classList.remove('btn-primary'); x.classList.add('btn-outline-light'); });
        b.classList.add('btn-primary'); b.classList.remove('btn-outline-light'); scene.background=backgrounds[b.dataset.bg]; });

    /* Cotización -> backend + WhatsApp */
    function designConfig(){
        return {
            colors: activeParts.map(p=>({ label:p.label, hex: p.mat&&p.mat.color? '#'+p.mat.color.getHexString():null })),
            patches: decals.length,
        };
    }
    // Captura 4 vistas girando la cámara: frente, lado derecho, espalda, lado izquierdo
    function captureViews(){
        const prevAuto=controls.autoRotate; controls.autoRotate=false;
        const prevPos=camera.position.clone(); const tgt=controls.target.clone();
        const r=camera.position.distanceTo(tgt), y=prevPos.y;
        const angles=[['frente',0],['derecha',Math.PI/2],['espalda',Math.PI],['izquierda',-Math.PI/2]];
        const out={};
        angles.forEach(([k,a])=>{
            camera.position.set(tgt.x + r*Math.sin(a), y, tgt.z + r*Math.cos(a));
            camera.lookAt(tgt); renderer.render(scene,camera);
            out[k]=renderer.domElement.toDataURL('image/png');
        });
        camera.position.copy(prevPos); camera.lookAt(tgt); controls.autoRotate=prevAuto; renderer.render(scene,camera);
        return out;
    }
    $('sendQuote').addEventListener('click', async ()=>{
        const name=$('cName').value.trim(), phone=$('cPhone').value.trim();
        const err=$('quoteError');
        if(!name || !phone){ err.textContent='Nombre y teléfono son obligatorios.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        const views=captureViews();
        const payload={ jacket_type_id:CFG.typeId, customer_name:name, customer_phone:phone,
            customer_email:$('cEmail').value.trim()||null, school:$('cSchool').value.trim()||null,
            quantity:qtyVal(), estimated_price:unitPrice()*qtyVal(), config:designConfig(),
            image:views.frente, images:views };
        const btn=$('sendQuote'); btn.disabled=true; btn.innerHTML='Enviando…';
        try{
            const r=await fetch(CFG.quoteUrl,{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json'}, body:JSON.stringify(payload) });
            const data=await r.json();
            if(data.ok){ window.open(data.whatsapp_url,'_blank'); bootstrap.Modal.getInstance($('quoteModal')).hide(); }
            else { err.textContent='No se pudo enviar. Revisa los datos.'; err.classList.remove('d-none'); }
        }catch(e){ err.textContent='Error de conexión: '+e.message; err.classList.remove('d-none'); }
        finally{ btn.disabled=false; btn.innerHTML='<i class="bi bi-send"></i> Enviar cotización'; }
    });

    /* ===== Chaqueta paramétrica por zonas (cuerpo, mangas, pretina, puños, cuello) ===== */
    function fabricMat(name,color){ const m=new THREE.MeshStandardMaterial({color:new THREE.Color(color),roughness:0.92,metalness:0.0}); m.name=name; return m; }
    function ribTexture(){ const c=document.createElement('canvas'); c.width=c.height=64; const x=c.getContext('2d');
        x.fillStyle='#ffffff'; x.fillRect(0,0,64,64); x.strokeStyle='rgba(0,0,0,.13)'; x.lineWidth=1.4;
        for(let i=2;i<64;i+=5){ x.beginPath(); x.moveTo(i,0); x.lineTo(i,64); x.stroke(); }
        const t=new THREE.CanvasTexture(c); t.wrapS=t.wrapT=THREE.RepeatWrapping; t.repeat.set(12,1); t.colorSpace=THREE.SRGBColorSpace; return t; }
    function stripeTexture(a,b){ const c=document.createElement('canvas'); c.width=16; c.height=96; const x=c.getContext('2d');
        x.fillStyle=a; x.fillRect(0,0,16,96); x.fillStyle=b; x.fillRect(0,34,16,28);
        const t=new THREE.CanvasTexture(c); t.wrapS=t.wrapT=THREE.RepeatWrapping; t.repeat.set(1,1); t.colorSpace=THREE.SRGBColorSpace; return t; }

    function applyRibStripes(on){
        const a=$('ribColorA').value, b=$('ribColorB').value;
        ribMats.forEach(m=>{
            if(on){ if(m.userData.prev===undefined) m.userData.prev='#'+m.color.getHexString(); m.map=stripeTexture(a,b); m.color.set('#ffffff'); }
            else { m.map=ribTexture(); if(m.userData.prev!==undefined){ m.color.set(m.userData.prev); m.userData.prev=undefined; } }
            m.needsUpdate=true;
        });
    }

    function buildParametricJacket(){
        const g=new THREE.Group();
        const matBody=fabricMat('Cuerpo','#1b2a4a');
        const matSleeve=fabricMat('Mangas','#101319');
        const matPretina=fabricMat('Pretina','#e0a422'); matPretina.map=ribTexture();
        const matCuff=fabricMat('Puños','#e0a422'); matCuff.map=ribTexture();
        const matCollar=fabricMat('Cuello','#e0a422'); matCollar.map=ribTexture();
        const matBtn=new THREE.MeshStandardMaterial({color:0x0b0b0b,roughness:0.35,metalness:0.6}); matBtn.name='Botones';
        ribMats.length=0; ribMats.push(matPretina,matCuff,matCollar);

        const pts=[new THREE.Vector2(0.95,-1.05),new THREE.Vector2(1.05,-0.4),new THREE.Vector2(1.04,0.4),new THREE.Vector2(0.92,0.95),new THREE.Vector2(0.6,1.18),new THREE.Vector2(0.5,1.25)];
        const body=new THREE.Mesh(new THREE.LatheGeometry(pts,48),matBody); body.scale.z=0.6; matBody.side=THREE.DoubleSide; g.add(body);

        const pret=new THREE.Mesh(new THREE.CylinderGeometry(1.0,0.98,0.42,48,1,true),matPretina); pret.position.y=-1.2; pret.scale.z=0.6; matPretina.side=THREE.DoubleSide; g.add(pret);

        const col=new THREE.Mesh(new THREE.TorusGeometry(0.52,0.15,14,40),matCollar); col.position.y=1.2; col.rotation.x=Math.PI/2; col.scale.z=0.6; g.add(col);

        [-1,1].forEach(s=>{
            const sl=new THREE.Mesh(new THREE.CylinderGeometry(0.42,0.34,1.9,28),matSleeve);
            sl.position.set(s*1.05,0.15,0); sl.rotation.z=s*0.42; g.add(sl);
            const end=new THREE.Vector3(0,-0.95,0).applyEuler(new THREE.Euler(0,0,s*0.42)).add(new THREE.Vector3(s*1.05,0.15,0));
            const cf=new THREE.Mesh(new THREE.CylinderGeometry(0.36,0.36,0.26,28),matCuff); cf.position.copy(end); cf.rotation.z=s*0.42; g.add(cf);
        });

        for(let k=0;k<4;k++){ const b=new THREE.Mesh(new THREE.CylinderGeometry(0.06,0.06,0.05,16),matBtn); b.rotation.x=Math.PI/2; b.position.set(0,0.55-k*0.42,0.635); g.add(b); }
        return g;
    }

    function useParametric(){
        if(current) scene.remove(current); clearDecals();
        current=buildParametricJacket(); scene.add(current);
        normalizeModel(current); frameCamera(); buildPartsUI(current);
        applyRibStripes($('ribStripes').checked);
        $('spin').classList.remove('on'); $('ribBox').style.display=''; controls.autoRotate=$('rotToggle').checked; updatePrice();
    }

    $('viewSeg').addEventListener('click',e=>{ const b=e.target.closest('button'); if(!b||b.disabled) return;
        $('viewSeg').querySelectorAll('button').forEach(x=>{ x.classList.remove('btn-primary'); x.classList.add('btn-outline-light'); });
        b.classList.add('btn-primary'); b.classList.remove('btn-outline-light');
        if(b.dataset.view==='real' && CFG.modelUrl){ $('ribBox').style.display='none'; loadModel(CFG.modelUrl); } else { useParametric(); } });
    $('ribStripes').addEventListener('change',e=>{ $('ribColors').classList.toggle('d-none',!e.target.checked); applyRibStripes(e.target.checked); });
    $('ribColorA').addEventListener('input',()=>{ if($('ribStripes').checked) applyRibStripes(true); });
    $('ribColorB').addEventListener('input',()=>{ if($('ribStripes').checked) applyRibStripes(true); });

    /* Rayas de la manga */
    $('stripeOn').addEventListener('change',()=>{ $('stripeOpts').style.opacity=$('stripeOn').checked?'1':'.5'; applyStripes(); });

    /* Init */
    window.addEventListener('resize',()=>{ camera.aspect=viewer.clientWidth/viewer.clientHeight; camera.updateProjectionMatrix(); renderer.setSize(viewer.clientWidth, viewer.clientHeight); });
    (function animate(){ requestAnimationFrame(animate); controls.update(); renderer.render(scene,camera); })();
    updatePrice();
    if(CFG.defaultView==='modelo' && CFG.modelUrl){
        // El .glb ya es editable por piezas: se usa como editor principal
        $('viewSegWrap').style.display='none';
        loadModel(CFG.modelUrl);
    } else {
        useParametric();   // chaqueta por zonas (cuerpo/mangas/pretina/puños/cuello)
    }
</script>
@endpush

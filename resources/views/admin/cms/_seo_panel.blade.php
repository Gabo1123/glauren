@php
    /* Panel SEO reutilizable. Requiere: $model (Post|Page), $type ('post'|'page').
       Debe ir DENTRO del <form> del editor. */
    $type = $type ?? 'page';
    $minWords = $type === 'post' ? 300 : 120;
@endphp

<div class="card p-4 mt-4 seo-panel" id="seoPanel" data-min-words="{{ $minWords }}" style="max-width:820px">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-family:var(--g-font-display);text-transform:uppercase;letter-spacing:.03em">
            <i class="bi bi-graph-up-arrow" style="color:var(--g-gold)"></i> SEO
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-secondary">Puntaje</span>
            <span id="seoScore" class="badge" style="font-size:14px;padding:7px 12px;background:var(--g-surface-3);color:var(--g-muted)">—</span>
        </div>
    </div>

    {{-- Vista previa Google --}}
    <div class="seo-preview mb-3">
        <div class="seo-prev-url" id="prevUrl">chaquetasdeprom.com › …</div>
        <div class="seo-prev-title" id="prevTitle">Título de la página</div>
        <div class="seo-prev-desc" id="prevDesc">Aquí verás cómo aparece tu página en los resultados de Google. Escribe una descripción atractiva con tu palabra clave.</div>
    </div>

    {{-- Palabra clave objetivo --}}
    <div class="mb-3">
        <label class="form-label small">Palabra clave objetivo <span class="text-secondary">(la frase por la que quieres posicionar)</span></label>
        <input type="text" name="focus_keyword" id="seoFocus" class="form-control" value="{{ old('focus_keyword', $model->focus_keyword) }}" placeholder="ej. chaquetas de prom Bogotá">
    </div>

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label small d-flex justify-content-between">
                <span>Título SEO</span><span class="seo-count" id="titleCount">0</span>
            </label>
            <input type="text" name="meta_title" id="seoTitle" class="form-control" value="{{ old('meta_title', $model->meta_title) }}" placeholder="Si lo dejas vacío, se usa el título de arriba">
        </div>
        <div class="col-md-12">
            <label class="form-label small d-flex justify-content-between">
                <span>Meta descripción</span><span class="seo-count" id="descCount">0</span>
            </label>
            <textarea name="meta_description" id="seoDesc" class="form-control" rows="3" placeholder="Resumen atractivo de 120–160 caracteres con la palabra clave.">{{ old('meta_description', $model->meta_description) }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label small">Imagen social (Open Graph) — URL</label>
            <input type="url" name="og_image" id="seoOg" class="form-control" value="{{ old('og_image', $model->og_image) }}" placeholder="https://…/imagen.jpg">
            <div class="small text-secondary mt-1">Se muestra al compartir en WhatsApp, Facebook, etc. @if($type==='post')Si la dejas vacía se usa la portada.@endif</div>
        </div>
        @if($type === 'page')
        <div class="col-md-6">
            <label class="form-label small">Tipo de schema</label>
            <select name="schema_type" class="form-select">
                @php $st = old('schema_type', $model->schema_type); @endphp
                <option value="" {{ $st ? '' : 'selected' }}>WebPage (normal)</option>
                <option value="Service" {{ $st==='Service'?'selected':'' }}>Service (servicio)</option>
                <option value="Product" {{ $st==='Product'?'selected':'' }}>Product (producto)</option>
                <option value="AboutPage" {{ $st==='AboutPage'?'selected':'' }}>AboutPage (sobre nosotros)</option>
                <option value="ContactPage" {{ $st==='ContactPage'?'selected':'' }}>ContactPage (contacto)</option>
            </select>
        </div>
        @endif
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="noindex" value="1" id="seoNoindex" {{ old('noindex', $model->noindex) ? 'checked' : '' }}>
                <label class="form-check-label small" for="seoNoindex">No indexar (ocultar de Google)</label>
            </div>
        </div>
    </div>

    {{-- Análisis --}}
    <div class="seo-analysis mt-4">
        <div class="small text-secondary mb-2" style="text-transform:uppercase;letter-spacing:.08em;font-size:11px">Análisis de la página</div>
        <ul id="seoChecks" class="list-unstyled mb-0"></ul>
    </div>
</div>

<style>
    .seo-preview{ background:#fff; border:1px solid var(--g-line); border-radius:10px; padding:14px 16px; }
    .seo-prev-url{ color:#202124; font-size:13px; }
    .seo-prev-title{ color:#1a0dab; font-size:19px; line-height:1.3; margin:2px 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .seo-prev-desc{ color:#4d5156; font-size:13px; line-height:1.5; }
    .seo-count{ font-size:11px; color:var(--g-muted); font-family:var(--g-font-mono,monospace); }
    .seo-count.ok{ color:var(--g-ok); }
    .seo-count.warn{ color:var(--g-warn); }
    .seo-count.bad{ color:#ff6b5e; }
    .seo-analysis li{ display:flex; align-items:flex-start; gap:9px; padding:6px 0; font-size:13.5px; border-bottom:1px solid var(--g-line); }
    .seo-analysis li:last-child{ border-bottom:0; }
    .seo-dot{ width:16px; height:16px; border-radius:50%; flex-shrink:0; margin-top:2px; display:grid; place-items:center; font-size:10px; color:#0d0d0f; font-weight:700; }
    .seo-dot.ok{ background:var(--g-ok); }
    .seo-dot.warn{ background:var(--g-warn); }
    .seo-dot.bad{ background:#ff6b5e; }
</style>

<script>
(function(){
    var panel = document.getElementById('seoPanel');
    if(!panel) return;
    var form  = panel.closest('form');
    var minWords = parseInt(panel.dataset.minWords || '120', 10);

    var q = function(sel){ return form.querySelector(sel); };
    var titleField = q('[name="title"]');
    var slugField  = q('[name="slug"]');
    var bodyField  = q('[name="body"]');
    var excerptF   = q('[name="excerpt"]');
    var coverF     = q('[name="cover"]');

    var fFocus = document.getElementById('seoFocus');
    var fTitle = document.getElementById('seoTitle');
    var fDesc  = document.getElementById('seoDesc');
    var fOg    = document.getElementById('seoOg');

    var prevUrl = document.getElementById('prevUrl');
    var prevTitle = document.getElementById('prevTitle');
    var prevDesc  = document.getElementById('prevDesc');
    var titleCount = document.getElementById('titleCount');
    var descCount  = document.getElementById('descCount');
    var scoreEl = document.getElementById('seoScore');
    var checksEl = document.getElementById('seoChecks');

    function slugify(s){
        return (s||'').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'')
            .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    }
    function textOf(html){ var d=document.createElement('div'); d.innerHTML=html||''; return (d.textContent||'').trim(); }
    function has(hay, needle){ return needle && hay.toLowerCase().indexOf(needle.toLowerCase()) !== -1; }

    function effTitle(){ return (fTitle.value.trim() || (titleField?titleField.value.trim():'')); }
    function effDesc(){
        var d = fDesc.value.trim();
        if(d) return d;
        if(excerptF && excerptF.value.trim()) return excerptF.value.trim();
        return textOf(bodyField?bodyField.value:'').slice(0,160);
    }

    function setCount(el, len, min, max){
        el.textContent = len;
        el.className = 'seo-count ' + (len>=min && len<=max ? 'ok' : (len>max ? 'bad' : 'warn'));
    }

    function render(){
        var fk = fFocus.value.trim();
        var title = effTitle();
        var desc = effDesc();
        var bodyText = textOf(bodyField ? bodyField.value : '');
        var bodyHtml = bodyField ? bodyField.value : '';
        var slug = (slugField && slugField.value.trim()) || slugify(title);
        var words = bodyText ? bodyText.split(/\s+/).filter(Boolean).length : 0;
        var hasImg = !!(fOg.value.trim()) || (coverF && coverF.files && coverF.files.length>0) || {{ ($type==='post' && $model->exists && $model->coverUrl()) || ($model->og_image ?? false) ? 'true' : 'false' }};

        // Preview
        prevUrl.textContent = 'chaquetasdeprom.com › ' + (slug || '…');
        prevTitle.textContent = title || 'Título de la página';
        prevDesc.textContent = desc || 'Escribe una meta descripción con tu palabra clave.';
        setCount(titleCount, title.length, 15, 60);
        setCount(descCount, desc.length, 70, 160);

        // Checks
        var checks = [];
        var firstPara = bodyText.slice(0, 140);
        var subheads = (bodyHtml.match(/<h[2-4][^>]*>[\s\S]*?<\/h[2-4]>/gi) || []).join(' ');

        if(fk){
            checks.push([has(title, fk), 'La palabra clave aparece en el título SEO.']);
            checks.push([has(desc, fk), 'La palabra clave aparece en la meta descripción.']);
            checks.push([has(slug, slugify(fk)) || has(slug, fk), 'La palabra clave aparece en la URL (slug).']);
            checks.push([has(bodyText, fk), 'La palabra clave aparece en el contenido.']);
            checks.push([has(firstPara, fk), 'La palabra clave aparece al comienzo del contenido.', 'warn']);
            checks.push([has(subheads, fk), 'La palabra clave aparece en un subtítulo (H2/H3).', 'warn']);
        } else {
            checks.push([false, 'Define una palabra clave objetivo para analizar la página.']);
        }
        checks.push([title.length>=15 && title.length<=60, 'El título SEO mide entre 15 y 60 caracteres.']);
        checks.push([desc.length>=70 && desc.length<=160, 'La meta descripción mide entre 70 y 160 caracteres.']);
        checks.push([words>=minWords, 'El contenido tiene al menos '+minWords+' palabras (tiene '+words+').', 'warn']);
        checks.push([hasImg, 'La página tiene imagen social / portada.', 'warn']);
        checks.push([/<a\s[^>]*href/i.test(bodyHtml), 'El contenido incluye al menos un enlace.', 'warn']);

        // Render list + score
        checksEl.innerHTML = '';
        var pass = 0, total = 0;
        checks.forEach(function(c){
            var ok = c[0], label = c[1], sev = c[2] || 'bad';
            total++; if(ok) pass++;
            var cls = ok ? 'ok' : sev;
            var li = document.createElement('li');
            li.innerHTML = '<span class="seo-dot '+cls+'">'+(ok?'✓':'!')+'</span><span>'+label+'</span>';
            checksEl.appendChild(li);
        });
        var score = total ? Math.round(pass/total*100) : 0;
        scoreEl.textContent = score + ' / 100';
        scoreEl.style.background = score>=80 ? 'rgba(37,211,102,.18)' : (score>=50 ? 'rgba(224,164,34,.18)' : 'rgba(255,107,94,.18)');
        scoreEl.style.color = score>=80 ? 'var(--g-ok)' : (score>=50 ? 'var(--g-warn)' : '#ff6b5e');
    }

    [fFocus,fTitle,fDesc,fOg,titleField,slugField,bodyField,excerptF,coverF].forEach(function(el){
        if(el){ el.addEventListener('input', render); el.addEventListener('change', render); }
    });
    render();
})();
</script>

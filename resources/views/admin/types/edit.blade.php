@extends('layouts.admin')
@section('title', $type->exists ? 'Editar tipo' : 'Nuevo tipo')

@section('content')
@php $isNew = ! $type->exists; @endphp
<h3 class="mb-4">{{ $isNew ? 'Nuevo tipo' : 'Editar: '.$type->name }}</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card p-4" style="max-width:640px">
    <form method="POST" action="{{ $isNew ? route('admin.types.store') : route('admin.types.update', $type) }}" enctype="multipart/form-data">
        @csrf
        @unless($isNew) @method('PUT') @endunless

        <div class="row g-3">
            <div class="col-9">
                <label class="form-label small">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
            </div>
            <div class="col-3">
                <label class="form-label small">Icono</label>
                <input type="text" name="icon" class="form-control" value="{{ old('icon', $type->icon) }}" maxlength="8">
            </div>

            @if($isNew)
            <div class="col-12">
                <label class="form-label small">Slug (URL, sin espacios)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="ej: varsity" required>
            </div>
            @endif

            <div class="col-8">
                <label class="form-label small">Descripción</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $type->description) }}</textarea>
            </div>
            <div class="col-4">
                <label class="form-label small">Categoría</label>
                <input type="text" name="category" class="form-control" list="catList" value="{{ old('category', $type->category) }}" placeholder="ej. Chaquetas">
                <datalist id="catList">
                    <option value="Chaquetas"><option value="Busos"><option value="Camisetas"><option value="Beisboleras"><option value="Accesorios">
                </datalist>
                <div class="small text-secondary mt-1">Para filtrar en /personalizar.</div>
            </div>

            <div class="col-4">
                <label class="form-label small">Precio base</label>
                <input type="number" step="0.01" name="base_price" class="form-control" value="{{ old('base_price', $type->base_price) }}" required>
            </div>
            <div class="col-4">
                <label class="form-label small">Precio por parche</label>
                <input type="number" step="0.01" name="patch_price" class="form-control" value="{{ old('patch_price', $type->patch_price) }}" required>
            </div>
            <div class="col-4">
                <label class="form-label small">Orden</label>
                <input type="number" name="sort" class="form-control" value="{{ old('sort', $type->sort) }}">
            </div>

            <div class="col-12">
                <label class="form-label small">Materiales, consumo y costo por chaqueta (uno por línea: <code>Nombre = cantidad unidad @ costo</code>)</label>
                @php
                    $compLines = collect(old('components_text') ? [] : $type->componentList())
                        ->map(function ($c) {
                            $line = $c['name'].' = '.rtrim(rtrim(number_format((float) $c['qty'], 2, '.', ''), '0'), '.').' '.($c['unit'] ?? 'u');
                            if (! empty($c['cost'])) { $line .= ' @ '.(int) $c['cost']; }
                            return $line;
                        })
                        ->implode("\n");
                @endphp
                <textarea name="components_text" class="form-control" rows="11" style="font-family:monospace;font-size:13px">{{ old('components_text', $compLines) }}</textarea>
                <div class="small text-secondary mt-1">
                    <code>m</code> = metros de tela (con % de desperdicio) · <code>u</code> = unidades · <code>@</code> = costo por metro/unidad (COP).<br>
                    Ejemplos: <code>Tela cuerpo = 1.2 m @ 18000</code> · <code>Botones = 5 u @ 500</code>. Pon <code>0</code> en lo que no aplique.
                </div>
            </div>

            <div class="col-12">
                <label class="form-label small">Modelo 3D (.glb / .gltf) — máx {{ config('chaquetas.max_model_mb') }}MB</label>
                <input type="file" name="model" id="modelFile" class="form-control" accept=".glb,.gltf">
                @if(! $isNew && $type->hasModel())
                    <div class="small text-success mt-1"><i class="bi bi-check-circle"></i> Modelo actual: <code>{{ $type->model_path }}</code>. Sube uno nuevo para reemplazarlo.</div>
                @endif
                <div class="mt-2">
                    <label class="form-label small">Vista por defecto en el diseñador</label>
                    <select name="default_view" class="form-select">
                        @php $dv = old('default_view', $type->default_view ?? 'modelo'); @endphp
                        <option value="modelo" {{ $dv==='modelo'?'selected':'' }}>Modelo 3D cargado (.glb con piezas editables)</option>
                        <option value="parametrico" {{ $dv==='parametrico'?'selected':'' }}>Chaqueta paramétrica (por zonas)</option>
                    </select>
                    <div class="small text-secondary mt-1">Usa «Modelo 3D» si tu .glb ya trae piezas editables (como el hoodie). «Paramétrica» arma una chaqueta por zonas sin modelo.</div>
                </div>
                <div class="small text-secondary mt-2" style="line-height:1.5">
                    <i class="bi bi-info-circle"></i> Solo <b>.glb</b> o <b>.gltf</b> (estándar web). Los <code>.blend</code>, <code>.zprj</code> (CLO 3D/Marvelous) y <code>.obj</code> <b>no</b> se suben directo: <b>expórtalos a .glb</b> conservando materiales y texturas.
                    <br>• <b>Blender:</b> Archivo → Exportar → glTF 2.0, formato <b>glTF Binary (.glb)</b>.
                    • <b>CLO 3D / Marvelous:</b> Export → glTF (.glb).
                    Recomendado <b>.glb</b> porque empaqueta todo en un solo archivo.
                </div>

                {{-- Visor 3D en vivo --}}
                <div class="model-view mt-2" id="modelView"
                     data-current="{{ (! $isNew && $type->hasModel()) ? '/storage/'.$type->model_path : '' }}">
                    <div class="mv-canvas"><canvas id="mvCanvas"></canvas></div>
                    <div class="mv-status" id="mvStatus"></div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $type->active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">Activo (visible para los clientes)</label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            <a href="{{ route('admin.types.index') }}" class="btn btn-outline-light">Cancelar</a>
        </div>
    </form>
</div>

<style>
    .model-view{ border:1px solid var(--g-line); border-radius:12px; overflow:hidden; background:radial-gradient(circle at 50% 30%, #2a2a33, #131318); }
    .mv-canvas{ height:340px; }
    #mvCanvas{ width:100%; height:100%; display:block; cursor:grab; }
    #mvCanvas:active{ cursor:grabbing; }
    .mv-status{ padding:8px 12px; font-size:12.5px; color:var(--g-muted); border-top:1px solid var(--g-line); background:var(--g-surface); min-height:34px; display:flex; align-items:center; gap:8px; }
    .mv-status.err{ color:#ff6b5e; }
    .mv-status.ok{ color:var(--g-ok); }
</style>

<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
  }
}
</script>
<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
    import { KTX2Loader } from 'three/addons/loaders/KTX2Loader.js';
    import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';

    const view = document.getElementById('modelView');
    const canvas = document.getElementById('mvCanvas');
    const statusEl = document.getElementById('mvStatus');
    const fileInput = document.getElementById('modelFile');

    function setStatus(msg, cls){ statusEl.className = 'mv-status' + (cls ? ' '+cls : ''); statusEl.innerHTML = msg; }

    const renderer = new THREE.WebGLRenderer({ canvas, antialias:true, alpha:true });
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

    const camera = new THREE.PerspectiveCamera(45, 1, 0.01, 1000);
    camera.position.set(0, 1, 3);
    const controls = new OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 1.2;

    scene.add(new THREE.HemisphereLight(0xffffff, 0x333333, 1.1));
    const key = new THREE.DirectionalLight(0xffffff, 1.4); key.position.set(3, 5, 4); scene.add(key);

    function resize(){
        const w = view.querySelector('.mv-canvas').clientWidth;
        const h = view.querySelector('.mv-canvas').clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h; camera.updateProjectionMatrix();
    }
    window.addEventListener('resize', resize); resize();

    (function loop(){ requestAnimationFrame(loop); controls.update(); renderer.render(scene, camera); })();

    const draco = new DRACOLoader(); draco.setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/');
    const ktx2 = new KTX2Loader().setTranscoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/basis/').detectSupport(renderer);
    const loader = new GLTFLoader();
    loader.setDRACOLoader(draco); loader.setKTX2Loader(ktx2); loader.setMeshoptDecoder(MeshoptDecoder);

    let current = null;
    function frameModel(obj){
        const box = new THREE.Box3().setFromObject(obj);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        obj.position.sub(center);
        const maxDim = Math.max(size.x, size.y, size.z) || 1;
        const dist = maxDim / (2 * Math.tan((camera.fov * Math.PI/180)/2));
        camera.position.set(0, size.y*0.1, dist*1.6);
        camera.near = maxDim/100; camera.far = maxDim*100; camera.updateProjectionMatrix();
        controls.target.set(0, 0, 0); controls.update();
    }

    function load(url, label){
        setStatus('<span class="spinner-border spinner-border-sm"></span> Cargando modelo…');
        loader.load(url, (gltf) => {
            if (current) scene.remove(current);
            current = gltf.scene; scene.add(current); frameModel(current);
            let meshes = 0, mats = new Set();
            current.traverse(o => { if (o.isMesh){ meshes++; if (o.material) mats.add(o.material.name || o.material.uuid); } });
            setStatus(`<i class="bi bi-check-circle"></i> ${label} cargado — ${meshes} malla(s), ${mats.size} material(es). Arrastra para rotar.`, 'ok');
        }, (ev) => {
            if (ev.total) setStatus(`<span class="spinner-border spinner-border-sm"></span> Cargando… ${Math.round(ev.loaded/ev.total*100)}%`);
        }, (err) => {
            console.error(err);
            setStatus('<i class="bi bi-exclamation-triangle"></i> No se pudo cargar el modelo. Revisa que sea un .glb/.gltf válido.', 'err');
        });
    }

    // Modelo guardado actual
    const currentUrl = view.dataset.current;
    if (currentUrl) { load(currentUrl, 'Modelo actual'); }
    else { setStatus('Sube un archivo .glb para previsualizarlo aquí.'); }

    // Previsualiza el archivo recién elegido (antes de guardar)
    fileInput?.addEventListener('change', () => {
        const f = fileInput.files && fileInput.files[0];
        if (!f) return;
        const url = URL.createObjectURL(f);
        load(url, 'Modelo nuevo (' + (f.size/1048576).toFixed(1) + ' MB)');
    });
</script>
@endsection

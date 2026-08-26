@extends('layouts.app')
@section('title', 'Diseña tu chaqueta de promoción')

@section('content')
<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold">Diseña tu chaqueta de promoción</h1>
        <p class="text-secondary fs-5">Elige el tipo, personalízala en 3D y pide tu cotización al instante.</p>
    </div>

    @if($categories->count() > 1)
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" id="catFilter">
        <button type="button" class="btn btn-sm btn-gold cat-btn active" data-cat="all">Todos</button>
        @foreach($categories as $cat)
            <button type="button" class="btn btn-sm btn-outline-secondary cat-btn" data-cat="{{ $cat }}">{{ $cat }}</button>
        @endforeach
    </div>
    @endif

    <div class="row g-4 justify-content-center" id="typeGrid">
        @foreach($types as $t)
            <div class="col-sm-6 col-lg-4 type-col" data-category="{{ $t->category ?: 'Otros' }}">
                <a href="{{ route('design', $t->slug) }}" class="text-decoration-none">
                    <div class="card h-100 type-card text-center position-relative">
                        @if($t->hasModel())
                            <span class="badge bg-success position-absolute" style="top:12px;right:12px;z-index:2">✓ 3D</span>
                        @else
                            <span class="badge bg-secondary position-absolute" style="top:12px;right:12px;z-index:2">Pronto</span>
                        @endif
                        @if($t->hasModel())
                            <div class="mini3d" data-model="/storage/{{ $t->model_path }}" data-icon="{{ $t->icon }}"></div>
                        @else
                            <div class="mini3d mini3d--empty">{{ $t->icon }}</div>
                        @endif
                        <div class="card-body pt-2 pb-4">
                            <h4 class="mt-1">{{ $t->name }}</h4>
                            @if($t->category)<div class="small text-secondary mb-1"><i class="bi bi-tag"></i> {{ $t->category }}</div>@endif
                            <p class="text-secondary small mb-3">{{ $t->description }}</p>
                            <div class="fw-bold text-success fs-6">Personalízala →</div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div id="noResults" class="text-center text-secondary py-5 d-none">No hay modelos en esta categoría.</div>

    <div class="text-center mt-5">
        <a href="{{ route('admin.login') }}" class="text-secondary small text-decoration-none"><i class="bi bi-lock"></i> Administrador</a>
    </div>
</div>

<style>
    .mini3d{ width:100%; height:210px; border-bottom:1px solid rgba(0,0,0,.06); background:radial-gradient(circle at 50% 35%, #2a2a33, #14141a); border-radius:8px 8px 0 0; position:relative; overflow:hidden; }
    .mini3d canvas{ width:100%!important; height:100%!important; display:block; cursor:grab; }
    .mini3d--empty{ display:grid; place-items:center; font-size:64px; }
    .mini3d .m3d-spin{ position:absolute; inset:0; display:grid; place-items:center; color:#9a9aa7; font-size:13px; }
    .type-card:hover .mini3d{ filter:brightness(1.05); }
</style>

<script type="importmap">
{ "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
}}
</script>
<script type="module">
    import * as THREE from 'three';
    import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
    import { KTX2Loader } from 'three/addons/loaders/KTX2Loader.js';
    import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';

    const viewers = [];
    let sharedEnv = null, envRenderer = null;

    function makeLoader(renderer){
        const draco = new DRACOLoader().setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/');
        const ktx2 = new KTX2Loader().setTranscoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/basis/').detectSupport(renderer);
        const loader = new GLTFLoader();
        loader.setDRACOLoader(draco); loader.setKTX2Loader(ktx2); loader.setMeshoptDecoder(MeshoptDecoder);
        return loader;
    }

    function initViewer(box){
        const url = box.dataset.model;
        box.innerHTML = '<div class="m3d-spin"><span class="spinner-border spinner-border-sm"></span></div>';
        const w = box.clientWidth || 300, h = box.clientHeight || 210;

        const renderer = new THREE.WebGLRenderer({ antialias:true, alpha:true, powerPreference:'low-power' });
        renderer.setPixelRatio(Math.min(devicePixelRatio, 1.6));
        renderer.setSize(w, h, false);
        renderer.outputColorSpace = THREE.SRGBColorSpace;

        const scene = new THREE.Scene();
        if(!sharedEnv){ const pmrem = new THREE.PMREMGenerator(renderer); sharedEnv = pmrem.fromScene(new RoomEnvironment(), 0.04).texture; }
        scene.environment = sharedEnv;
        scene.add(new THREE.HemisphereLight(0xffffff, 0x333340, 1.0));
        const key = new THREE.DirectionalLight(0xffffff, 1.3); key.position.set(3, 5, 4); scene.add(key);

        const camera = new THREE.PerspectiveCamera(42, w/h, 0.01, 1000);
        const v = { renderer, scene, camera, model:null, visible:true, box, spin:true };
        viewers.push(v);

        // drag para rotar manual
        let down=false, lastX=0;
        renderer.domElement.addEventListener('pointerdown', e=>{ down=true; lastX=e.clientX; v.spin=false; });
        window.addEventListener('pointerup', ()=>{ down=false; });
        window.addEventListener('pointermove', e=>{ if(down && v.model){ v.model.rotation.y += (e.clientX-lastX)*0.01; lastX=e.clientX; } });

        makeLoader(renderer).load(url, gltf=>{
            const obj = gltf.scene;
            const box3 = new THREE.Box3().setFromObject(obj); const size = box3.getSize(new THREE.Vector3()); const c = box3.getCenter(new THREE.Vector3());
            const maxDim = Math.max(size.x, size.y, size.z) || 1; obj.scale.setScalar(2.6/maxDim);
            obj.position.sub(c.multiplyScalar(2.6/maxDim));
            scene.add(obj); v.model = obj;
            camera.position.set(0, 0.2, 3.4);
            box.innerHTML=''; box.appendChild(renderer.domElement);
        }, undefined, err=>{ console.error(err); box.classList.add('mini3d--empty'); box.innerHTML = box.dataset.icon || '🧥'; });
    }

    // Lazy: inicia el visor cuando la tarjeta entra en pantalla
    const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
            const box = e.target;
            box._vis = e.isIntersecting;
            if(e.isIntersecting && !box._loaded){ box._loaded = true; initViewer(box); }
        });
    }, { rootMargin:'150px' });
    const allBoxes = [...document.querySelectorAll('.mini3d[data-model]')];
    allBoxes.forEach(b=>io.observe(b));
    // Carga inmediata de las primeras (arriba del pliegue), por si el observer tarda
    setTimeout(()=>{ allBoxes.slice(0,3).forEach(b=>{ if(!b._loaded){ b._loaded=true; b._vis=true; initViewer(b); } }); }, 300);

    // Un solo loop que anima solo los visores visibles
    (function loop(){
        requestAnimationFrame(loop);
        viewers.forEach(v=>{
            if(!v.box._vis) return;
            if(v.model && v.spin) v.model.rotation.y += 0.008;
            v.renderer.render(v.scene, v.camera);
        });
    })();
</script>

<script>
(function(){
    var filter = document.getElementById('catFilter');
    if(!filter) return;
    var cols = Array.prototype.slice.call(document.querySelectorAll('.type-col'));
    var noRes = document.getElementById('noResults');
    filter.addEventListener('click', function(e){
        var b = e.target.closest('.cat-btn'); if(!b) return;
        filter.querySelectorAll('.cat-btn').forEach(function(x){ x.classList.remove('btn-gold','active'); x.classList.add('btn-outline-secondary'); });
        b.classList.add('btn-gold','active'); b.classList.remove('btn-outline-secondary');
        var cat = b.dataset.cat, shown = 0;
        cols.forEach(function(c){ var ok=(cat==='all'||c.dataset.category===cat); c.style.display=ok?'':'none'; if(ok) shown++; });
        noRes.classList.toggle('d-none', shown>0);
    });
})();
</script>
@endsection

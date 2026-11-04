{{--
    Guía paso a paso para el cliente.
    @include('partials.guia', ['guide' => 'firma', 'at' => 0])
      guide → clave en config/guias.php
      at    → índice del paso actual (0 = primero). Si es mayor que el último, todo queda hecho.
      open  → (opcional) abierta al cargar, por defecto sí
--}}
@php
    $g = config('guias.'.$guide);
    $steps = $g['steps'] ?? [];
    $total = count($steps);
    $at = max(0, (int) ($at ?? 0));
    $allDone = $at >= $total;
    $cur = $steps[min($at, $total - 1)] ?? null;
    $pct = $total ? (int) round((min($at + ($allDone ? 0 : 1), $total)) / $total * 100) : 0;
    $open = $open ?? true;
    $compact = $compact ?? false;   // compacta: solo "Estás aquí"; los demás pasos se despliegan
@endphp
@if($g && $total)
@once
<style>
.gx{--gy:#FFC72C;--go:#FF6B1A;--gk:#0E0E0E;font-family:inherit;margin:0 0 20px;border-radius:18px;background:linear-gradient(160deg,#17171c,#101013);border:1px solid rgba(255,199,44,.35);color:#f4efe3;overflow:hidden;text-align:left}
.gx summary{list-style:none;cursor:pointer;padding:16px 18px;display:flex;align-items:center;gap:14px}
.gx summary::-webkit-details-marker{display:none}
.gx .gx-ic{flex:0 0 44px;height:44px;border-radius:12px;background:var(--gy);color:var(--gk);display:grid;place-items:center;font-size:22px}
.gx .gx-k{display:block;font-size:10.5px;font-weight:800;letter-spacing:.16em;color:var(--gy);text-transform:uppercase}
.gx .gx-t{display:block;font-size:17px;font-weight:800;line-height:1.2;margin-top:2px}
.gx .gx-tg{margin-left:auto;font-size:12px;color:#a7a7b1;white-space:nowrap;display:flex;align-items:center;gap:6px}
.gx .gx-tg:after{content:"▾";font-size:14px;transition:transform .2s}
.gx[open] .gx-tg:after{transform:rotate(180deg)}
.gx .gx-bar{height:4px;background:rgba(255,255,255,.08)}
.gx .gx-bar i{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--gy),var(--go));transition:width .6s}
.gx .gx-body{padding:16px 18px 18px}
.gx .gx-intro{font-size:13.5px;color:#c9c9d1;margin:0 0 14px;line-height:1.5}
.gx .gx-now{background:rgba(255,199,44,.1);border:1.5px solid var(--gy);border-radius:14px;padding:14px 16px;margin-bottom:16px}
.gx .gx-now .lb{display:inline-block;background:var(--go);color:var(--gk);font-size:10.5px;font-weight:800;letter-spacing:.12em;padding:3px 9px;border-radius:999px;margin-bottom:8px}
.gx .gx-now h4{margin:0 0 4px;font-size:17px;font-weight:800;color:#fff}
.gx .gx-now p{margin:0 0 10px;font-size:13.5px;color:#d9d9df;line-height:1.5}
.gx .gx-how{margin:0;padding:0;list-style:none;counter-reset:h;display:flex;flex-direction:column;gap:8px}
.gx .gx-how li{counter-increment:h;display:flex;gap:10px;font-size:13.5px;line-height:1.45;color:#f4efe3}
.gx .gx-how li:before{content:counter(h);flex:0 0 22px;height:22px;border-radius:50%;background:var(--gy);color:var(--gk);font-weight:800;font-size:12px;display:grid;place-items:center}
.gx .gx-steps{margin:0;padding:0;list-style:none;position:relative}
.gx .gx-st{position:relative;display:flex;gap:12px;padding:0 0 14px}
.gx .gx-st:not(:last-child):before{content:"";position:absolute;left:15px;top:32px;bottom:0;width:2px;background:rgba(255,255,255,.1)}
.gx .gx-st.done:not(:last-child):before{background:var(--gy)}
.gx .gx-n{flex:0 0 32px;height:32px;border-radius:50%;display:grid;place-items:center;font-size:15px;background:#26262e;border:2px solid rgba(255,255,255,.12);position:relative;z-index:1}
.gx .gx-st.done .gx-n{background:var(--gy);border-color:var(--gy);color:var(--gk);font-weight:800}
.gx .gx-st.cur .gx-n{background:var(--go);border-color:var(--go);box-shadow:0 0 0 5px rgba(255,107,26,.2)}
.gx .gx-st b{display:block;font-size:14px;color:#fff;margin-top:5px}
.gx .gx-st span{display:block;font-size:12.5px;color:#a7a7b1;line-height:1.45;margin-top:2px}
.gx .gx-st.todo b{color:#c9c9d1}
.gx .gx-st details{margin-top:4px}
.gx .gx-st details summary{padding:0;font-size:12px;color:var(--gy);display:inline}
.gx .gx-st details ul{margin:6px 0 0;padding-left:18px;font-size:12.5px;color:#c9c9d1;line-height:1.5}
.gx .gx-all{margin:-4px 0 12px}
.gx .gx-all>summary{padding:0 0 12px;font-size:13px;font-weight:700;color:var(--gy);display:block}
.gx .gx-all>summary:after{content:" ▾"}
.gx .gx-all[open]>summary:after{content:" ▴"}
.gx .gx-tip{margin-top:4px;background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.18);border-radius:12px;padding:10px 12px;font-size:12.5px;color:#c9c9d1;line-height:1.5}
.gx .gx-done{background:rgba(37,211,102,.12);border:1.5px solid #25d366;border-radius:14px;padding:14px 16px;margin-bottom:16px;font-weight:700;color:#c9f7da}
@media print{.gx{display:none!important}}
</style>
@endonce
<details class="gx" {{ $open ? 'open' : '' }}>
    <summary>
        <span class="gx-ic">🧭</span>
        <span>
            <span class="gx-k">Guía paso a paso</span>
            <span class="gx-t">{{ $g['title'] }}</span>
        </span>
        <span class="gx-tg">{{ $allDone ? '¡Completo!' : 'Paso '.($at + 1).' de '.$total }}</span>
    </summary>
    <div class="gx-bar"><i style="width:{{ $pct }}%"></i></div>
    <div class="gx-body">
        @if(! empty($g['intro']))<p class="gx-intro">{{ $g['intro'] }}</p>@endif

        @if($allDone)
            <div class="gx-done">🎉 ¡Listo! Completaste todos los pasos.</div>
        @elseif($cur)
            <div class="gx-now">
                <span class="lb">ESTÁS AQUÍ · PASO {{ $at + 1 }}</span>
                <h4>{{ $cur['icon'] ?? '' }} {{ $cur['title'] }}</h4>
                <p>{{ $cur['text'] }}</p>
                @if(! empty($cur['how']))
                    <div style="font-size:11px;font-weight:800;letter-spacing:.12em;color:#FFC72C;margin-bottom:8px">QUÉ HACER AHORA</div>
                    <ol class="gx-how">@foreach($cur['how'] as $h)<li>{{ $h }}</li>@endforeach</ol>
                @endif
            </div>
        @endif

        @if($compact)<details class="gx-all"><summary>Ver los {{ $total }} pasos</summary>@endif
        <ol class="gx-steps">
            @foreach($steps as $i => $st)
                @php $state = $i < $at ? 'done' : ($i === $at ? 'cur' : 'todo'); @endphp
                <li class="gx-st {{ $state }}">
                    <span class="gx-n">{{ $state === 'done' ? '✓' : ($st['icon'] ?? $i + 1) }}</span>
                    <div>
                        <b>{{ $i + 1 }}. {{ $st['title'] }}</b>
                        <span>{{ $st['text'] }}</span>
                        @if($state !== 'cur' && ! empty($st['how']))
                            <details><summary>¿Qué se hace aquí?</summary><ul>@foreach($st['how'] as $h)<li>{{ $h }}</li>@endforeach</ul></details>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
        @if($compact)</details>@endif

        @if(! empty($g['tip']))<div class="gx-tip">💡 {{ $g['tip'] }}</div>@endif
    </div>
</details>
@endif

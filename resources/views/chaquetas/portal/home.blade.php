@php
    use App\Modules\Chaquetas\Models\Setting;
    use App\Modules\Chaquetas\Models\Contract;
    $brand = Setting::get('brand_name','Glauren');
    $c = $student->contract;
    $stages = Contract::STAGES; $totalStages = count($stages);
    $idx = $c?->stageIndex() ?? 0;
    $pct = $c ? (int) round((($idx+1)/$totalStages)*100) : 0;
    $allPaid = $c ? $c->allStudentsPaid() : false;
    $eta = ($c && $allPaid) ? $c->deliveryDate() : null;
    $myPosts = $posts->where('student_id',$student->id)->count();
    $likesRecibidos = $posts->where('student_id',$student->id)->sum('likes_count');
    $schoolPays = $c?->school?->schoolPays();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi portal · {{ $brand }}</title><meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#17171d;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.1)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif}
    .top{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--line);position:sticky;top:0;background:rgba(13,13,15,.94);backdrop-filter:blur(8px);z-index:10}
    .top .b{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;color:var(--gold);font-size:22px;text-decoration:none}
    .lo{background:none;border:1px solid var(--line);color:var(--muted);padding:7px 13px;border-radius:8px;cursor:pointer;font-size:13px}
    .wrap{max-width:680px;margin:0 auto;padding:0 16px 60px}
    .card{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:20px;margin-bottom:18px}
    .ok{background:rgba(37,211,102,.12);border:1px solid var(--ok);color:#bff5d0;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:14px}
    .err{background:rgba(255,74,61,.12);border:1px solid #ff4a3d;color:#ffb0a8;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:14px}
    /* Header premium con portada */
    .cover{position:relative;border-radius:0 0 20px 20px;background:
        radial-gradient(600px 240px at 20% -20%,rgba(233,184,63,.35),transparent 60%),
        linear-gradient(120deg,#1c1c22,#2a2015);border-bottom:1px solid var(--line);padding:40px 16px 0;margin-bottom:70px}
    .cover-in{max-width:680px;margin:0 auto;position:relative}
    .av-big{position:absolute;bottom:-48px;left:0;width:96px;height:96px;border-radius:50%;border:4px solid var(--ink);background:var(--gold);color:#1a1405;display:grid;place-items:center;font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:40px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.5)}
    .av-big img{width:100%;height:100%;object-fit:cover}
    .cover .name{padding:6px 0 20px 112px;min-height:56px}
    .cover .name h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:24px;line-height:1}
    .cover .name span{color:#cfc9bd;font-size:13px}
    .profstats{display:flex;gap:20px;padding:0 4px 16px 112px}
    .profstats .s b{font-family:'Big Shoulders Display',sans-serif;font-size:20px;display:block;line-height:1}
    .profstats .s span{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.05em}
    /* Estado del pedido */
    .status-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
    .st-item{background:var(--panel2);border:1px solid var(--line);border-radius:10px;padding:12px 14px}
    .st-item .k{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
    .st-item .v{font-family:'Big Shoulders Display',sans-serif;font-weight:700;font-size:16px;margin-top:2px}
    .prog{height:9px;background:var(--panel2);border-radius:999px;overflow:hidden;margin:12px 0 4px}
    .prog i{display:block;height:100%;background:var(--gold)}
    .h2{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:19px;margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .link{color:var(--gold);background:none;border:0;cursor:pointer;font-size:13px;font-family:'Space Mono',monospace;padding:0}
    .btn{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:11px 18px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:15px;cursor:pointer;box-shadow:4px 4px 0 #000;text-decoration:none;display:inline-block}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin:10px 0 5px;text-transform:uppercase}
    input[type=text],textarea,input[type=file]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:10px;border-radius:8px;font-size:14px;font-family:inherit}
    input:focus,textarea:focus{outline:none;border-color:var(--gold)}
    details.edit summary{cursor:pointer;color:var(--gold);font-size:13px;font-family:'Space Mono',monospace;list-style:none}
    details.edit summary::-webkit-details-marker{display:none}
    /* Compañeros */
    .mates{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px}
    .mate{flex:0 0 auto;text-align:center;width:64px}
    .mate .av{width:52px;height:52px;border-radius:50%;background:rgba(233,184,63,.14);border:1px solid var(--gold);display:grid;place-items:center;font-size:22px;overflow:hidden;margin:0 auto 5px}
    .mate .av img{width:100%;height:100%;object-fit:cover}
    .mate span{font-size:11px;color:#cfc9bd;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    /* Muro */
    .post{display:flex;gap:12px;padding:16px 0;border-bottom:1px solid var(--line)}
    .post:last-child{border-bottom:none}
    .pav{width:42px;height:42px;border-radius:50%;background:rgba(233,184,63,.14);border:1px solid var(--gold);display:grid;place-items:center;font-size:20px;overflow:hidden;flex:none}
    .pav img{width:100%;height:100%;object-fit:cover}
    .pbody{flex:1;min-width:0}
    .pname{font-weight:700;font-size:14px}.ptime{color:var(--muted);font-size:11px;font-family:'Space Mono',monospace}
    .ptext{font-size:15px;margin-top:3px;color:#e7e3d8;white-space:pre-wrap;word-wrap:break-word}
    .pimg{margin-top:10px;border-radius:12px;max-width:100%;border:1px solid var(--line)}
    .pact{display:flex;align-items:center;gap:16px;margin-top:10px}
    .likebtn{background:none;border:0;cursor:pointer;color:var(--muted);font-size:14px;display:inline-flex;align-items:center;gap:6px;padding:0}
    .likebtn.on{color:#ff5a6a}
    .likebtn:hover{color:#ff5a6a}
    .del{background:none;border:0;color:var(--muted);cursor:pointer;font-size:12px}.del:hover{color:#ff4a3d}
    .empty{color:var(--muted);text-align:center;padding:24px 0;font-size:14px}
    .comments{margin-top:10px;padding-top:8px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:6px}
    .comment{font-size:13px;color:#cfc9bd}.comment b{color:var(--paper)}
    .cform{display:flex;gap:8px;margin-top:8px}
    .cform input{flex:1;background:var(--panel2);border:1px solid var(--line);color:var(--paper);border-radius:999px;padding:8px 14px;font-size:13px}
    .cform input:focus{outline:none;border-color:var(--gold)}
    .cform button{background:var(--gold);color:#1c1503;border:0;border-radius:999px;padding:0 14px;font-weight:700;cursor:pointer;font-size:13px}
    /* ===== Navegación por pestañas (app) ===== */
    body{padding-bottom:84px}
    .sp-nav{position:fixed;left:0;right:0;bottom:0;z-index:90;display:flex;justify-content:space-around;background:rgba(13,13,15,.96);backdrop-filter:blur(10px);border-top:1px solid var(--line);padding:8px 6px calc(8px + env(safe-area-inset-bottom))}
    .sp-nav a{flex:1;max-width:120px;display:flex;flex-direction:column;align-items:center;gap:3px;color:var(--muted);text-decoration:none;font-size:11px;font-weight:600;padding:6px 2px;border-radius:12px;transition:.15s}
    .sp-nav a span{font-size:20px;line-height:1;filter:grayscale(1);opacity:.7;transition:.15s}
    .sp-nav a.on{color:var(--gold);background:rgba(233,184,63,.1)}
    .sp-nav a.on span{filter:none;opacity:1;transform:translateY(-1px) scale(1.08)}
    .h2 .cnt{margin-left:auto;background:#ff5a4d;color:#fff;font-family:'Space Mono',monospace;font-size:12px;border-radius:999px;padding:1px 9px}
    .todo{display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;background:var(--panel2);border:1px solid var(--line);text-decoration:none;color:var(--paper);margin-bottom:8px;transition:.15s}
    .todo:hover{border-color:var(--gold)}
    button.todo{color:var(--paper)}
    .todo .ti{flex:0 0 40px;height:40px;border-radius:12px;background:rgba(233,184,63,.14);display:grid;place-items:center;font-size:20px}
    .todo .tt{flex:1;min-width:0}.todo .tt b{display:block;font-size:14.5px}.todo .tt small{color:var(--muted);font-size:12.5px}
    .todo .ta{color:var(--gold);font-size:18px}
    .card.mini .mt{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:18px}
    .mates.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(76px,1fr));gap:14px;overflow:visible}
    .mates.grid .mate{width:auto}
    .mates.grid .mate .av{width:62px;height:62px}
    @media(min-width:760px){ .sp-nav{left:50%;right:auto;transform:translateX(-50%);bottom:16px;border:1px solid var(--line);border-radius:18px;width:560px;padding:8px} body{padding-bottom:100px} }
    /* Toast */
    #toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%) translateY(120px);background:var(--gold);color:#1c1503;font-weight:700;padding:13px 22px;border-radius:999px;box-shadow:0 10px 30px rgba(0,0,0,.5);z-index:100;transition:transform .4s cubic-bezier(.2,.8,.2,1);font-size:14px;max-width:90vw}
    #toast.show{transform:translateX(-50%) translateY(0)}
    #toast.err{background:#ff4a3d;color:#fff}
</style>
</head>
<body>
    <div class="top">
        <a href="{{ route('home') }}" class="b">{{ $brand }}</a>
        <form method="POST" action="{{ route('student.logout') }}">@csrf<button class="lo">Salir</button></form>
    </div>

    {{-- Header premium --}}
    <div class="cover">
        <div class="cover-in">
            <div class="av-big">@if($student->photoUrl())<img src="{{ $student->photoUrl() }}" alt="">@else🙂@endif</div>
            <div class="name">
                <h1>{{ $student->nickname ?: $student->name }}</h1>
                <span>{{ $c?->school?->name ?? 'Mi colegio' }}@if($c?->year) · Prom {{ $c->year }}@endif</span>
            </div>
            <div class="profstats">
                <div class="s"><b>{{ $myPosts }}</b><span>Posts</span></div>
                <div class="s"><b>{{ $likesRecibidos }}</b><span>Me gusta</span></div>
                <div class="s"><b>{{ $classmates->count()+1 }}</b><span>En el curso</span></div>
            </div>
        </div>
    </div>

    <div class="wrap">
    <div class="sp-pane" data-pane="inicio">
        {{-- Tus pendientes --}}
        @php
            $todo = [];
            if (! $student->photoUrl()) { $todo[] = ['📸', 'Sube tu foto de perfil', 'Así te reconocen en el muro y en el anuario.', 'perfil']; }
            if ($designOptions->count() && $canVote && ! $myVote) { $todo[] = ['🗳️', 'Vota el diseño de tu curso', 'Elige tu favorito antes de que cierre la votación.', 'chaqueta']; }
            if ($c && ! $schoolPays && ! $student->isPaid() && ! $student->isUnderReview()) { $todo[] = ['💳', 'Haz tu pago', 'Así tu chaqueta no se retrasa.', 'pago']; }
            if (! $myPosts) { $todo[] = ['✍️', 'Escribe en el muro', 'Saluda a tu curso o sube una foto.', 'muro']; }
            if ($c && $c->production_status === 'entregado') { array_unshift($todo, ['⭐', 'Califica tu chaqueta', '1 minuto: cuéntanos cómo te fue y súbenos fotos de la entrega.', 'encuesta']); }
        @endphp
        <div class="card">
            <div class="h2">✅ Tus pendientes @if(count($todo))<span class="cnt">{{ count($todo) }}</span>@endif</div>
            @forelse($todo as [$ic, $t, $d, $go])
                @if($go === 'pago')
                    <form method="POST" action="{{ route('seguimiento.search') }}">@csrf<input type="hidden" name="document" value="{{ $student->document }}">
                        <button type="submit" class="todo" style="width:100%;text-align:left;cursor:pointer;font:inherit"><span class="ti">{{ $ic }}</span><span class="tt"><b>{{ $t }}</b><small>{{ $d }}</small></span><span class="ta">→</span></button>
                    </form>
                @elseif($go === 'encuesta')
                    <a href="{{ \App\Modules\Chaquetas\Support\DeliverySurvey::url($c) }}" class="todo"><span class="ti">{{ $ic }}</span><span class="tt"><b>{{ $t }}</b><small>{{ $d }}</small></span><span class="ta">→</span></a>
                @else
                    <a href="#{{ $go }}" data-go="{{ $go }}" class="todo"><span class="ti">{{ $ic }}</span><span class="tt"><b>{{ $t }}</b><small>{{ $d }}</small></span><span class="ta">→</span></a>
                @endif
            @empty
                <div style="color:var(--ok);font-size:14px">🎉 ¡Estás al día! No tienes nada pendiente.</div>
            @endforelse
        </div>
        @if($c)
        <a href="#chaqueta" data-go="chaqueta" class="card mini" style="display:block;text-decoration:none;color:var(--paper)">
            <div style="display:flex;justify-content:space-between;align-items:center"><b class="mt">🧵 Mi chaqueta</b><span style="color:var(--gold);font-family:'Space Mono',monospace;font-size:12px">{{ $pct }}% →</span></div>
            <div class="prog"><i style="width:{{ $pct }}%"></i></div>
            <div style="color:var(--muted);font-size:13px">Etapa: <b style="color:var(--paper)">{{ $c->stageLabel() }}</b>@if($eta && $c->production_status !== 'entregado') · Entrega: <b style="color:var(--paper)">{{ $eta->locale('es')->isoFormat('D MMM') }}</b>@endif</div>
        </a>
        @endif
        @if($c)
            @include('partials.guia', ['guide' => 'portal_estudiante', 'at' => $c->production_status === 'entregado' ? 99 : $idx, 'open' => true, 'compact' => true])
        @endif
        {{-- Anuario --}}
        <a href="{{ route('student.yearbook') }}" style="display:flex;align-items:center;gap:12px;background:linear-gradient(120deg,#2a2015,#1c1c22);border:1px solid var(--gold);border-radius:14px;padding:16px 18px;margin-top:0;margin-bottom:18px;text-decoration:none;color:var(--paper)">
            <span style="font-size:28px">📖</span>
            <div style="flex:1"><b style="font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:17px">Anuario del curso</b><div style="color:var(--muted);font-size:12px">Todos los compañeros y las fotos de la promoción</div></div>
            <span style="color:var(--gold)">→</span>
        </a>


    </div>
    <div class="sp-pane" data-pane="chaqueta">
        {{-- Estado de mi chaqueta --}}
        @if($c)
        <div class="card">
            <div class="h2">🧵 Mi chaqueta <span style="margin-left:auto;font-size:12px;color:var(--gold);font-family:'Space Mono',monospace">{{ $pct }}%</span></div>
            <div class="prog"><i style="width:{{ $pct }}%"></i></div>
            <div style="color:var(--muted);font-size:13px">Etapa: <b style="color:var(--paper)">{{ $c->stageLabel() }}</b></div>
            <div class="status-grid">
                <div class="st-item"><div class="k">Prenda</div><div class="v">{{ $c->jacketType?->name ?? 'Chaqueta' }}</div></div>
                @if($c->production_status==='entregado')
                    <div class="st-item"><div class="k">🚚 Entrega</div><div class="v">Entregada ✓</div></div>
                @elseif($eta)
                    <div class="st-item"><div class="k">🚚 Entrega</div><div class="v">{{ $eta->locale('es')->isoFormat('D MMM YYYY') }}</div></div>
                @else
                    <div class="st-item"><div class="k">🚚 Entrega</div><div class="v" style="font-size:13px;color:var(--muted)">Cuando el curso complete el pago</div></div>
                @endif
                <div class="st-item"><div class="k">💳 Pago</div><div class="v">{{ $schoolPays ? 'Lo paga el colegio' : ($student->isPaid()?'Pagado ✓':($student->isUnderReview()?'En revisión':'Pendiente')) }}</div></div>
                <div class="st-item"><div class="k">Tallas</div><div class="v">{{ $student->hasMeasurements()?'Registradas ✓':'Faltan' }}</div></div>
            </div>
            @if(! $schoolPays && ! $student->isPaid())
                <form method="POST" action="{{ route('seguimiento.search') }}" style="margin-top:14px">@csrf
                    <input type="hidden" name="document" value="{{ $student->document }}">
                    <button class="btn" style="width:100%">{{ $student->isUnderReview() ? '🧾 Ver mi pago en revisión' : '💳 Pagar mi chaqueta o subir comprobante' }}</button>
                </form>
            @endif
            <a href="{{ route('seguimiento') }}" class="link" style="display:inline-block;margin-top:12px">Ver seguimiento completo →</a>
        </div>
        @endif

        {{-- Diseño final aprobado --}}
        @if(isset($finalDesign) && $finalDesign->count())
        <div class="card">
            <div class="h2">🎨 El diseño final de tu chaqueta</div>
            <p style="color:var(--muted);font-size:13px;margin-bottom:12px">Así quedará tu prenda. ¡Este es el diseño aprobado para tu promoción!</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px">
                @foreach($finalDesign as $d)
                    <a href="{{ $d->url() }}" target="_blank" style="display:block;border-radius:12px;overflow:hidden;border:1px solid var(--line)">
                        <img src="{{ $d->url() }}" alt="Diseño final" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover;display:block">
                        @if($d->caption)<div style="padding:6px 8px;font-size:12px;color:var(--muted)">{{ $d->caption }}</div>@endif
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Votación de diseño --}}
        @if($designOptions->count())
        <div class="card">
            <div class="h2">🗳️ {{ $canVote ? 'Vota el diseño de tu curso' : 'Resultado de la votación' }} @if($myVote && $canVote)<span style="margin-left:auto;font-size:12px;color:var(--ok);font-family:'Space Mono',monospace">✓ Ya votaste</span>@endif</div>
            @if(!$canVote)<p style="color:var(--muted);font-size:13px;margin-bottom:12px">La votación está cerrada.</p>
            @elseif(!$myVote)<p style="color:var(--muted);font-size:13px;margin-bottom:12px">Elige tu favorito. Puedes cambiar tu voto.</p>@endif
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px">
                @foreach($designOptions as $op)
                    @php $vp = $totalDesignVotes ? round($op->votes_count/$totalDesignVotes*100) : 0; $mine = $myVote === $op->id; @endphp
                    <form method="POST" action="{{ route('student.vote', $op) }}">
                        @csrf
                        <button type="submit" {{ $canVote ? '' : 'disabled' }} style="width:100%;background:none;border:2px solid {{ $mine ? 'var(--gold)' : 'var(--line)' }};border-radius:12px;padding:0;overflow:hidden;cursor:{{ $canVote ? 'pointer' : 'default' }};text-align:left">
                            <img src="{{ $op->imageUrl() }}" alt="{{ $op->label }}" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;background:#fff">
                            <div style="padding:8px 10px">
                                <div style="display:flex;justify-content:space-between;align-items:center">
                                    <b style="color:var(--paper);font-size:13px">{{ $op->label }}</b>
                                    @if($mine)<span style="color:var(--gold);font-size:12px">❤️ Tu voto</span>@endif
                                </div>
                                @if($myVote && $showResults)
                                    <div style="height:6px;background:var(--panel2);border-radius:999px;overflow:hidden;margin-top:6px"><div style="height:100%;width:{{ $vp }}%;background:var(--gold)"></div></div>
                                    <span style="color:var(--muted);font-size:11px">{{ $op->votes_count }} votos · {{ $vp }}%</span>
                                @elseif($myVote)
                                    <span style="color:var(--muted);font-size:11px">{{ $mine ? '✓ Tu voto' : '' }}</span>
                                @else
                                    <span style="color:var(--gold);font-size:12px">Votar →</span>
                                @endif
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    <div class="sp-pane" data-pane="muro">
        {{-- Publicar --}}
        <div class="card">
            <div class="h2">✍️ Publica en el muro</div>
            <form method="POST" action="{{ route('student.post') }}" enctype="multipart/form-data">
                @csrf
                <textarea name="body" rows="2" maxlength="500" placeholder="Escribe algo para tu curso…"></textarea>
                <label>Agregar foto (opcional)</label>
                <input type="file" name="photo" accept="image/*">
                <button class="btn" style="margin-top:12px">Publicar</button>
            </form>
        </div>

        {{-- Muro --}}
        <div class="card">
            <div class="h2">📣 Muro de {{ $c?->school?->name ?? 'tu curso' }}</div>
            @forelse($posts as $p)
                @php $liked = in_array($p->id, $likedIds); @endphp
                <div class="post">
                    <div class="pav">@if($p->student && $p->student->photoUrl())<img src="{{ $p->student->photoUrl() }}" alt="">@else🙂@endif</div>
                    <div class="pbody">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="pname">{{ $p->student?->nickname ?: ($p->student?->name ?? 'Estudiante') }}</span>
                            <span class="ptime">{{ $p->created_at->diffForHumans() }}</span>
                        </div>
                        @if($p->body)<div class="ptext">{{ $p->body }}</div>@endif
                        @if($p->photoUrl())<img src="{{ $p->photoUrl() }}" class="pimg" alt="foto" loading="lazy">@endif
                        <div class="pact">
                            <form method="POST" action="{{ route('student.post.like', $p) }}">@csrf
                                <button class="likebtn {{ $liked ? 'on' : '' }}" type="submit">{!! $liked ? '❤️' : '🤍' !!} {{ $p->likes_count ?: '' }} Me gusta</button>
                            </form>
                            @if($p->student_id === $student->id)
                                <form method="POST" action="{{ route('student.post.delete', $p) }}" onsubmit="return confirm('¿Borrar?')">@csrf @method('DELETE')<button class="del">Borrar</button></form>
                            @endif
                        </div>
                        @if($p->comments->count())
                        <div class="comments">
                            @foreach($p->comments as $cm)
                                <div class="comment"><b>{{ $cm->student?->nickname ?: explode(' ',$cm->student?->name ?? 'Alumno')[0] }}:</b> {{ $cm->body }}</div>
                            @endforeach
                        </div>
                        @endif
                        <form method="POST" action="{{ route('student.comment', $p) }}" class="cform">@csrf
                            <input type="text" name="body" maxlength="400" placeholder="Escribe un comentario…" required>
                            <button type="submit">›</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty">Aún no hay publicaciones. ¡Sé el primero! 🎉</div>
            @endforelse
        </div>
    </div>
    <div class="sp-pane" data-pane="curso">
        {{-- Compañeros del curso --}}
        @if($classmates->count())
        <div class="card">
            <div class="h2">🎓 Tu curso</div>
            <div class="mates grid">
                @foreach($classmates as $cm)
                    <div class="mate">
                        <div class="av">@if($cm->photoUrl())<img src="{{ $cm->photoUrl() }}" alt="">@else🙂@endif</div>
                        <span>{{ $cm->nickname ?: explode(' ',$cm->name)[0] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Anuario --}}
        <a href="{{ route('student.yearbook') }}" style="display:flex;align-items:center;gap:12px;background:linear-gradient(120deg,#2a2015,#1c1c22);border:1px solid var(--gold);border-radius:14px;padding:16px 18px;margin-top:0;text-decoration:none;color:var(--paper)">
            <span style="font-size:28px">📖</span>
            <div style="flex:1"><b style="font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:17px">Anuario del curso</b><div style="color:var(--muted);font-size:12px">Todos los compañeros y las fotos de la promoción</div></div>
            <span style="color:var(--gold)">→</span>
        </a>


    </div>
    <div class="sp-pane" data-pane="perfil">
        <div class="card">
            <div class="h2">👤 Mi perfil</div>
            <div style="display:flex;gap:14px;align-items:center;margin-bottom:6px">
                <div class="pav" style="width:64px;height:64px;font-size:28px">@if($student->photoUrl())<img src="{{ $student->photoUrl() }}" alt="">@else🙂@endif</div>
                <div>
                    <b style="font-size:17px">{{ $student->name }}</b>
                    <div style="color:var(--muted);font-size:13px">@if($student->nickname)“{{ $student->nickname }}” · @endif{{ $c?->school?->name ?? 'Mi colegio' }}</div>
                </div>
            </div>
            <div style="color:var(--muted);font-size:12px;margin-bottom:6px">Tu nombre sale así en la chaqueta; si está mal escrito, avísale a tu colegio.</div>
        </div>
        {{-- Editar perfil --}}
        <div class="card">
            <details class="edit" open>
                <summary>✏️ Editar mi perfil</summary>
                <form method="POST" action="{{ route('student.profile') }}" enctype="multipart/form-data" style="margin-top:10px">
                    @csrf
                    <label>Apodo</label>
                    <input type="text" name="nickname" value="{{ $student->nickname }}" maxlength="60" placeholder="Ej: Vale">
                    <label>Sobre mí</label>
                    <textarea name="bio" rows="2" maxlength="200" placeholder="Una frase tuya…">{{ $student->bio }}</textarea>
                    <label>Foto de perfil</label>
                    <input type="file" name="photo" accept="image/*">
                    <button class="btn" style="margin-top:12px">Guardar perfil</button>
                </form>
            </details>
            @if($student->bio)<div style="color:#cfc9bd;font-size:14px;margin-top:10px">“{{ $student->bio }}”</div>@endif
        </div>

    </div>
    </div>

    {{-- Menú inferior --}}
    <nav class="sp-nav" id="spNav">
        <a href="#inicio" data-tab="inicio"><span>🏠</span>Inicio</a>
        <a href="#chaqueta" data-tab="chaqueta"><span>🧵</span>Mi chaqueta</a>
        <a href="#muro" data-tab="muro"><span>📣</span>Muro</a>
        <a href="#curso" data-tab="curso"><span>🎓</span>Curso</a>
        <a href="#perfil" data-tab="perfil"><span>👤</span>Perfil</a>
    </nav>

    {{-- Toast --}}
    @if(session('ok'))<div id="toast" data-msg="{{ session('ok') }}"></div>
    @elseif($errors->any())<div id="toast" class="err" data-msg="{{ $errors->first() }}"></div>@endif

    {{-- Onboarding (primera vez) --}}
    <div id="onb" style="position:fixed;inset:0;background:rgba(6,7,10,.85);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;padding:22px;z-index:120">
        <div style="max-width:400px;background:var(--panel);border:2px solid var(--gold);border-radius:18px;padding:30px 26px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.6)">
            <div style="font-size:44px">🎓</div>
            <h2 style="font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:26px;margin:8px 0">¡Bienvenido a tu portal!</h2>
            <div style="color:#cfc9bd;font-size:14px;line-height:1.7;text-align:left;margin:16px 0">
                🧵 Sigue el <b>avance de tu chaqueta</b><br>
                🗳️ <b>Vota</b> el diseño de tu curso<br>
                📸 Sube fotos y <b>comenta</b> en el muro<br>
                ❤️ Dale <b>me gusta</b> a tus compañeros<br>
                📖 Revisa el <b>anuario</b> de la promoción
            </div>
            <button onclick="document.getElementById('onb').style.display='none';try{localStorage.setItem('glauren_onb','1')}catch(e){}" style="width:100%;background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:10px;padding:13px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:17px;cursor:pointer;box-shadow:4px 4px 0 #000">¡Entendido!</button>
        </div>
    </div>

    <script>
        // Pestañas del portal
        (function(){
            var tabs=document.querySelectorAll('#spNav [data-tab]'), panes=document.querySelectorAll('.sp-pane');
            function show(n,top){ var ok=false; panes.forEach(function(p){var on=p.dataset.pane===n; p.hidden=!on; ok=ok||on;}); if(!ok) return show('inicio',top);
                tabs.forEach(function(t){t.classList.toggle('on',t.dataset.tab===n)}); if(history.replaceState) history.replaceState(null,'','#'+n); if(top) window.scrollTo({top:document.querySelector('.wrap').offsetTop-70,behavior:'smooth'}); }
            tabs.forEach(function(t){t.addEventListener('click',function(e){e.preventDefault(); show(t.dataset.tab,true);})});
            document.querySelectorAll('[data-go]').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault(); show(a.dataset.go,true);})});
            show((location.hash||'#inicio').slice(1), !!location.hash);
            window.addEventListener('hashchange',function(){ show((location.hash||'#inicio').slice(1), true); });
        })();
        // Toast
        (function(){var t=document.getElementById('toast'); if(!t||!t.dataset.msg) return; t.textContent=t.dataset.msg; setTimeout(function(){t.classList.add('show')},120); setTimeout(function(){t.classList.remove('show')},3600);})();
        // Onboarding una vez
        (function(){var seen=false; try{seen=localStorage.getItem('glauren_onb')==='1'}catch(e){} if(!seen){document.getElementById('onb').style.display='flex';}})();
    </script>
</body>
</html>

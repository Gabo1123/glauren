@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); $c = $student->contract; @endphp
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
    .wrap{max-width:640px;margin:0 auto;padding:22px 16px 60px}
    .card{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:20px;margin-bottom:18px}
    .ok{background:rgba(37,211,102,.12);border:1px solid var(--ok);color:#bff5d0;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:14px}
    .err{background:rgba(255,74,61,.12);border:1px solid #ff4a3d;color:#ffb0a8;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:14px}
    /* Perfil */
    .prof{display:flex;gap:16px;align-items:center}
    .av{width:74px;height:74px;border-radius:50%;background:rgba(233,184,63,.14);border:2px solid var(--gold);display:grid;place-items:center;font-size:34px;overflow:hidden;flex:none}
    .av img{width:100%;height:100%;object-fit:cover}
    .prof h1{font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:24px;line-height:1}
    .prof .meta{color:var(--muted);font-size:13px;margin-top:3px}
    .prof .bio{color:#cfc9bd;font-size:14px;margin-top:6px}
    .link{color:var(--gold);background:none;border:0;cursor:pointer;font-size:13px;font-family:'Space Mono',monospace;padding:0;margin-top:8px}
    details.edit{margin-top:12px;border-top:1px solid var(--line);padding-top:12px}
    label{font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);display:block;margin:10px 0 5px;text-transform:uppercase}
    input[type=text],textarea,input[type=file]{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);padding:10px;border-radius:8px;font-size:14px;font-family:inherit}
    input:focus,textarea:focus{outline:none;border-color:var(--gold)}
    .btn{background:var(--gold);color:#1c1503;border:2px solid #000;border-radius:8px;padding:11px 18px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:15px;cursor:pointer;box-shadow:4px 4px 0 #000;margin-top:12px}
    .btn:hover{transform:translate(2px,2px);box-shadow:2px 2px 0 #000}
    .h2{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:20px;margin-bottom:12px}
    /* Muro */
    .post{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--line)}
    .post:last-child{border-bottom:none}
    .pav{width:42px;height:42px;border-radius:50%;background:rgba(233,184,63,.14);border:1px solid var(--gold);display:grid;place-items:center;font-size:20px;overflow:hidden;flex:none}
    .pav img{width:100%;height:100%;object-fit:cover}
    .pbody{flex:1;min-width:0}
    .pname{font-weight:700;font-size:14px}.ptime{color:var(--muted);font-size:11px;font-family:'Space Mono',monospace}
    .ptext{font-size:15px;margin-top:3px;color:#e7e3d8;white-space:pre-wrap;word-wrap:break-word}
    .pimg{margin-top:10px;border-radius:12px;max-width:100%;border:1px solid var(--line)}
    .del{background:none;border:0;color:var(--muted);cursor:pointer;font-size:12px}.del:hover{color:#ff4a3d}
    .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .chip{background:var(--panel2);border:1px solid var(--line);border-radius:999px;padding:6px 12px;font-size:12px;color:#cfc9bd}
    .empty{color:var(--muted);text-align:center;padding:24px 0;font-size:14px}
    .track{display:inline-block;margin-top:8px;color:var(--gold);text-decoration:none;font-size:13px;font-family:'Space Mono',monospace}
</style>
</head>
<body>
    <div class="top">
        <a href="{{ route('home') }}" class="b">{{ $brand }}</a>
        <form method="POST" action="{{ route('student.logout') }}">@csrf<button class="lo">Salir</button></form>
    </div>

    <div class="wrap">
        @if(session('ok'))<div class="ok">✅ {{ session('ok') }}</div>@endif
        @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif

        {{-- Perfil --}}
        <div class="card">
            <div class="prof">
                <div class="av">@if($student->photoUrl())<img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}">@else🙂@endif</div>
                <div>
                    <h1>{{ $student->nickname ?: $student->name }}</h1>
                    <div class="meta">{{ $c?->school?->name ?? 'Mi colegio' }}@if($c?->year) · Prom {{ $c->year }}@endif</div>
                    @if($student->bio)<div class="bio">{{ $student->bio }}</div>@endif
                </div>
            </div>
            <a href="{{ route('seguimiento') }}" class="track">📦 Ver el avance de mi chaqueta →</a>

            <details class="edit">
                <summary class="link">✏️ Editar mi perfil</summary>
                <form method="POST" action="{{ route('student.profile') }}" enctype="multipart/form-data">
                    @csrf
                    <label>Apodo (cómo te ven)</label>
                    <input type="text" name="nickname" value="{{ $student->nickname }}" maxlength="60" placeholder="Ej: Vale">
                    <label>Sobre mí</label>
                    <textarea name="bio" rows="2" maxlength="200" placeholder="Una frase tuya…">{{ $student->bio }}</textarea>
                    <label>Foto de perfil</label>
                    <input type="file" name="photo" accept="image/*">
                    <button class="btn">Guardar perfil</button>
                </form>
            </details>
        </div>

        {{-- Publicar --}}
        <div class="card">
            <div class="h2">✍️ Publica en el muro de tu curso</div>
            <form method="POST" action="{{ route('student.post') }}" enctype="multipart/form-data">
                @csrf
                <textarea name="body" rows="2" maxlength="500" placeholder="Escribe algo para tu curso…"></textarea>
                <label>Agregar foto (opcional)</label>
                <input type="file" name="photo" accept="image/*">
                <button class="btn">Publicar</button>
            </form>
            @if($classmates->count())
            <div class="chips">
                <span class="chip">🎓 {{ $classmates->count() + 1 }} en tu curso</span>
                @foreach($classmates->take(6) as $cm)<span class="chip">{{ $cm->nickname ?: explode(' ',$cm->name)[0] }}</span>@endforeach
            </div>
            @endif
        </div>

        {{-- Muro --}}
        <div class="card">
            <div class="h2">📣 Muro de {{ $c?->school?->name ?? 'tu curso' }}</div>
            @forelse($posts as $p)
                <div class="post">
                    <div class="pav">@if($p->student && $p->student->photoUrl())<img src="{{ $p->student->photoUrl() }}" alt="">@else🙂@endif</div>
                    <div class="pbody">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="pname">{{ $p->student?->nickname ?: ($p->student?->name ?? 'Estudiante') }}</span>
                            <span class="ptime">{{ $p->created_at->diffForHumans() }}</span>
                        </div>
                        @if($p->body)<div class="ptext">{{ $p->body }}</div>@endif
                        @if($p->photoUrl())<img src="{{ $p->photoUrl() }}" class="pimg" alt="foto" loading="lazy">@endif
                        @if($p->student_id === $student->id)
                            <form method="POST" action="{{ route('student.post.delete', $p) }}" onsubmit="return confirm('¿Borrar esta publicación?')" style="margin-top:6px">@csrf @method('DELETE')<button class="del">Borrar</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty">Aún no hay publicaciones. ¡Sé el primero en escribir a tu curso! 🎉</div>
            @endforelse
        </div>
    </div>
</body>
</html>

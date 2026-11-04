@php $school = $contract->school?->name; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>¿Cómo te fue con G'Lauren?</title>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@800;900&family=Hanken+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink) radial-gradient(700px 380px at 85% -10%,rgba(233,184,63,.18),transparent 60%);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif;line-height:1.55;min-height:100vh;padding:28px 16px}
    .card{max-width:560px;margin:0 auto;background:var(--panel);border:2px solid #000;border-radius:16px;box-shadow:6px 6px 0 #000;padding:28px}
    .mk{display:inline-grid;place-items:center;width:42px;height:42px;border-radius:9px;background:var(--gold);color:#1c1503;font-family:'Big Shoulders Display';font-weight:900;font-size:22px;border:2px solid #000}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:38px;line-height:.95;margin:16px 0 8px}
    .sub{color:var(--muted);margin-bottom:22px}
    label{display:block;font-weight:700;font-size:14px;margin:18px 0 7px}
    input[type=text],textarea,select{width:100%;background:var(--ink);border:1px solid var(--line);color:var(--paper);padding:12px;border-radius:9px;font:inherit}
    .stars{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:6px}
    .stars input{display:none}
    .stars label{margin:0;font-size:42px;line-height:1;color:#3a3a44;cursor:pointer;transition:transform .1s}
    .stars label:hover,.stars label:hover~label,.stars input:checked~label{color:var(--gold)}
    .stars label:active{transform:scale(.9)}
    .chk{display:flex;gap:10px;align-items:flex-start;font-weight:400;color:var(--muted)}
    .chk input{margin-top:4px;width:18px;height:18px;accent-color:var(--gold)}
    .btn{display:flex;width:100%;align-items:center;justify-content:center;gap:8px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:20px;padding:14px;border:2px solid #000;border-radius:10px;box-shadow:4px 4px 0 #000;cursor:pointer;margin-top:24px;text-decoration:none}
    .gold{background:var(--gold);color:#1c1503}.g{background:#fff;color:#1f1f1f}
    .err{background:rgba(255,90,90,.12);border:1px solid rgba(255,90,90,.4);color:#ffb3b3;padding:10px 12px;border-radius:9px;margin-bottom:12px;font-size:14px}
    .hp{position:absolute;left:-9999px}
    .big{font-size:56px;text-align:center;margin:6px 0}
</style>
</head>
<body>
<div class="card">
    <span class="mk">G</span>
    @if($done)
        <div class="big">{{ str_repeat('⭐', $done) }}</div>
        <h1>¡Gracias! 💛</h1>
        <p class="sub">Tu opinión ya nos llegó. Nos ayuda a mejorar y a que otros colegios nos conozcan.</p>
        @if($google && $done >= 4)
            <p>¿Nos regalas 30 segundos más? Una reseña en Google es lo que más nos ayuda:</p>
            <a href="{{ $google }}" target="_blank" rel="noopener" class="btn g">⭐ Dejar reseña en Google</a>
        @endif
        <a href="{{ url('/') }}" class="btn gold" style="margin-top:14px">Ir a chaquetasdeprom.com</a>
    @else
        <h1>¿Cómo te fue con tus chaquetas?</h1>
        <p class="sub">{{ $school ? 'Promoción '.$school.' · ' : '' }}Toma 1 minuto. Si quieres, súbenos fotos de la entrega.</p>
        @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input class="hp" name="website" tabindex="-1" autocomplete="off">
            <label>Califica la experiencia</label>
            <div class="stars">
                @for($i = 5; $i >= 1; $i--)
                    <input type="radio" name="rating" id="r{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}><label for="r{{ $i }}" title="{{ $i }}">★</label>
                @endfor
            </div>
            <label for="name">Tu nombre</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="80" required>
            <label for="role">Eres…</label>
            <select id="role" name="role">
                <option value="Estudiante">Estudiante</option>
                <option value="Padre o madre de familia">Padre o madre de familia</option>
                <option value="Docente o coordinador">Docente o coordinador</option>
                <option value="Rector(a)">Rector(a)</option>
            </select>
            <label for="text">¿Qué te gustó? ¿Qué mejorarías?</label>
            <textarea id="text" name="text" rows="4" maxlength="1200" placeholder="Las chaquetas quedaron…">{{ old('text') }}</textarea>
            <label for="photos">Fotos de la entrega (opcional, hasta 6)</label>
            <input type="file" id="photos" name="photos[]" accept="image/*" multiple style="width:100%;color:var(--muted)">
            <label class="chk"><input type="checkbox" name="publish" value="1" checked> Autorizo a G'Lauren a publicar mi comentario y fotos en su web y redes.</label>
            <button class="btn gold">Enviar mi opinión</button>
        </form>
    @endif
</div>
</body>
</html>

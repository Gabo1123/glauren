@php
    use App\Modules\Chaquetas\Models\Setting;
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
    $phone = Setting::get('contact_phone', '3246750207');
    $addr = Setting::get('contact_address', 'Bogotá, Colombia');
    $email = Setting::get('contact_email', 'glauren@chaquetasdeprom.com');
    $hours = Setting::get('contact_hours', 'Lun–Sáb · 8am – 6pm');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'contacto',
    'seoTitleOverride' => 'Contáctanos · Glauren',
    'seoDescriptionOverride' => 'Contáctanos para cotizar las chaquetas y busos de prom de tu colegio. Asesoría sin compromiso en toda Colombia.',
    'breadcrumbs' => [['name'=>'Inicio','url'=>url('/')], ['name'=>'Contáctanos','url'=>route('contacto')]],
])
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono&display=swap" rel="stylesheet">
<link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
<style>
    :root{--ink:#0d0d0f;--panel:#1a1a20;--panel2:#22222a;--paper:#f4efe3;--gold:#e9b83f;--ok:#25d366;--hot:#ff4a3d;--muted:#9a9aa7;--line:rgba(244,239,227,.14)}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--ink);color:var(--paper);font-family:'Hanken Grotesk',system-ui,sans-serif}
    .wrap{max-width:1040px;margin:0 auto;padding:44px 24px 80px}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.2em;font-size:12px;color:var(--gold)}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(38px,7vw,72px);line-height:.86;margin:10px 0 8px}
    .lead{color:var(--muted);max-width:560px;margin-bottom:30px;font-size:17px}
    .grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px}
    @media(max-width:820px){.grid{grid-template-columns:1fr}}
    .card{background:var(--panel);border:3px solid #000;border-radius:14px;padding:28px;box-shadow:10px 10px 0 var(--gold)}
    label{font-family:'Space Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);display:block;margin:12px 0 6px}
    input,textarea{width:100%;background:var(--panel2);border:2px solid #000;color:var(--paper);border-radius:8px;padding:12px 13px;font-size:15px;font-family:inherit}
    input:focus,textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,184,63,.2)}
    .btn{width:100%;margin-top:18px;background:var(--ok);color:#04240f;border:2px solid #000;border-radius:10px;padding:15px;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;letter-spacing:.03em;font-size:19px;cursor:pointer;box-shadow:5px 5px 0 #000;transition:transform .1s,box-shadow .1s}
    .btn:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .sent{background:rgba(37,211,102,.12);border:2px solid var(--ok);color:#bff5d0;border-radius:10px;padding:14px 16px;margin-bottom:18px;font-weight:600}
    .err{background:rgba(255,74,61,.12);border:2px solid var(--hot);color:#ffb0a8;border-radius:8px;padding:11px 13px;margin-bottom:14px;font-size:13px}
    .side .row{display:flex;gap:13px;align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--line)}
    .side .ic{width:44px;height:44px;flex-shrink:0;border-radius:11px;background:rgba(233,184,63,.12);border:1.5px solid var(--gold);display:grid;place-items:center;font-size:20px}
    .side .row b{font-family:'Big Shoulders Display',sans-serif;text-transform:uppercase;font-size:15px;display:block;letter-spacing:.03em}
    .side .row a,.side .row span{color:var(--muted);font-size:14px;text-decoration:none}
    .side .row a:hover{color:var(--gold)}
    .wabig{display:flex;align-items:center;justify-content:center;gap:10px;background:var(--ok);color:#04240f;border:2px solid #000;box-shadow:5px 5px 0 #000;border-radius:10px;padding:14px;margin-top:18px;text-decoration:none;font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase}
    .wabig:hover{transform:translate(2px,2px);box-shadow:3px 3px 0 #000}
    .days{display:flex;gap:8px;overflow-x:auto;padding-bottom:6px}
    .day-chip{flex:0 0 auto;display:flex;flex-direction:column;align-items:center;gap:1px;background:var(--panel2);border:2px solid #000;border-radius:10px;padding:9px 12px;cursor:pointer;color:var(--paper);min-width:62px}
    .day-chip .dn{font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;color:var(--muted)}
    .day-chip .dd{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:22px;line-height:1}
    .day-chip .dm{font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;color:var(--muted)}
    .day-chip.on{background:var(--gold);color:#1c1503}
    .day-chip.on .dn,.day-chip.on .dm{color:#5a4708}
    .slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(78px,1fr));gap:8px;margin-top:4px}
    .slot{background:var(--panel2);border:2px solid #000;border-radius:8px;padding:11px 0;text-align:center;cursor:pointer;color:var(--paper);font-family:'Big Shoulders Display',sans-serif;font-weight:800;font-size:16px}
    .slot:hover{border-color:var(--gold)}
    .slot.on{background:var(--gold);color:#1c1503}
    .slots .empty{grid-column:1/-1;color:var(--muted);font-size:13px;padding:8px 0}
</style>
</head>
<body>
@include('chaquetas.partials.topbar')
<div class="wrap">
    <span class="ey">// Estamos para ayudarte</span>
    <h1>Contáctanos</h1>
    <p class="lead">Cuéntanos de tu promoción y te asesoramos <b>sin compromiso</b>. Te respondemos rapidísimo por WhatsApp.</p>

    <div class="grid">
        <div class="card">
            @if(session('sent'))<div class="sent">✅ ¡Recibido! Te contactaremos muy pronto. Si es urgente, escríbenos por WhatsApp.</div>@endif
            @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('contacto.submit') }}">
                @csrf
                <label>Nombre *</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div><label>WhatsApp / Teléfono *</label><input type="text" name="phone" value="{{ old('phone') }}" required></div>
                    <div><label>Correo</label><input type="email" name="email" value="{{ old('email') }}"></div>
                </div>
                <label>Colegio / Curso</label>
                <input type="text" name="school" value="{{ old('school') }}" placeholder="Ej: Colegio San José · 11B">
                <label>Cuéntanos qué necesitas</label>
                <textarea name="message" rows="4" placeholder="Cantidad de estudiantes, tipo de chaqueta, fecha estimada…">{{ old('message') }}</textarea>

                {{-- Agendar reunión --}}
                <div class="sched">
                    <label style="display:flex;align-items:center;gap:9px;cursor:pointer;margin-top:22px">
                        <input type="checkbox" id="wantMeet" style="width:20px;height:20px;flex:none;accent-color:var(--gold)">
                        <span style="color:var(--paper);font-family:'Big Shoulders Display',sans-serif;font-weight:800;text-transform:uppercase;font-size:18px;letter-spacing:.03em">📅 Quiero agendar una reunión</span>
                    </label>

                    <div id="schedBox" style="display:none;margin-top:14px">
                        <label>Elige el día</label>
                        <div class="days" id="days">
                            @php
                                $days = [];
                                $d = \Carbon\Carbon::today();
                                while (count($days) < 10) {
                                    $d = $d->copy()->addDay();
                                    if (! $d->isSunday()) { $days[] = $d->copy(); }
                                }
                            @endphp
                            @foreach($days as $day)
                                <button type="button" class="chip day-chip" data-date="{{ $day->toDateString() }}">
                                    <span class="dn">{{ ucfirst($day->locale('es')->isoFormat('ddd')) }}</span>
                                    <span class="dd">{{ $day->isoFormat('D') }}</span>
                                    <span class="dm">{{ $day->locale('es')->isoFormat('MMM') }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div id="slotsWrap" style="display:none">
                            <label>Elige la hora (9am – 6pm)</label>
                            <div class="slots" id="slots"></div>
                        </div>
                        <input type="hidden" name="date" id="fDate">
                        <input type="hidden" name="time" id="fTime">
                    </div>
                </div>

                <button class="btn" id="submitBtn">Enviar mensaje</button>
            </form>
        </div>

        <div class="side">
            <div class="card" style="box-shadow:8px 8px 0 #000">
                <div class="row"><div class="ic">💬</div><div><b>WhatsApp</b><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">+{{ $wa }}</a></div></div>
                <div class="row"><div class="ic">📞</div><div><b>Teléfono</b><a href="tel:{{ $phone }}">{{ $phone }}</a></div></div>
                <div class="row"><div class="ic">✉️</div><div><b>Correo</b><a href="mailto:{{ $email }}">{{ $email }}</a></div></div>
                <div class="row"><div class="ic">🕐</div><div><b>Horario</b><span>{{ $hours }}</span></div></div>
                <div class="row" style="border-bottom:none"><div class="ic">📍</div><div><b>Dónde estamos</b><span>{{ $addr }}</span></div></div>
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Hola Glauren, quiero cotizar las chaquetas de mi promoción.') }}" target="_blank" rel="noopener" class="wabig">💬 Escríbenos ahora</a>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var want=document.getElementById('wantMeet'), box=document.getElementById('schedBox');
    var days=document.getElementById('days'), slotsWrap=document.getElementById('slotsWrap'), slots=document.getElementById('slots');
    var fDate=document.getElementById('fDate'), fTime=document.getElementById('fTime');
    if(!want) return;

    want.addEventListener('change', function(){ box.style.display=want.checked?'block':'none'; if(!want.checked){clearSel();} });

    function clearSel(){ fDate.value=''; fTime.value=''; slotsWrap.style.display='none'; slots.innerHTML='';
        days.querySelectorAll('.day-chip.on').forEach(function(c){c.classList.remove('on');}); }

    days.querySelectorAll('.day-chip').forEach(function(chip){
        chip.addEventListener('click', function(){
            days.querySelectorAll('.day-chip.on').forEach(function(c){c.classList.remove('on');});
            chip.classList.add('on');
            fDate.value=chip.dataset.date; fTime.value='';
            slots.innerHTML='<div class="empty">Buscando horarios…</div>'; slotsWrap.style.display='block';
            fetch('{{ route('contacto.slots') }}?date='+chip.dataset.date)
                .then(function(r){return r.json();})
                .then(function(j){ renderSlots(j.slots||[]); })
                .catch(function(){ slots.innerHTML='<div class="empty">No se pudo cargar. Intenta de nuevo.</div>'; });
        });
    });

    function renderSlots(list){
        if(!list.length){ slots.innerHTML='<div class="empty">Sin horarios libres ese día. Elige otro 🙌</div>'; return; }
        slots.innerHTML='';
        list.forEach(function(t){
            var b=document.createElement('div'); b.className='slot'; b.textContent=t;
            b.addEventListener('click', function(){
                slots.querySelectorAll('.slot.on').forEach(function(s){s.classList.remove('on');});
                b.classList.add('on'); fTime.value=t;
            });
            slots.appendChild(b);
        });
    }
})();
</script>
</body>
</html>

@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); $money = fn($v)=>'$'.number_format((float)$v,0,',','.'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Certificado · {{ $school->name }}</title><meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Hanken+Grotesk:wght@400;600&family=Space+Mono&display=swap" rel="stylesheet">
<style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Hanken Grotesk',sans-serif;background:#eef0f3;padding:24px;color:#1a1a1a}
    .cert{max-width:820px;margin:0 auto;background:#0d0d0f;color:#f4efe3;border:10px solid #e9b83f;border-radius:6px;padding:56px 54px;text-align:center;position:relative;box-shadow:0 10px 40px rgba(0,0,0,.2)}
    .cert::before{content:'';position:absolute;inset:14px;border:1px solid rgba(233,184,63,.4);border-radius:3px;pointer-events:none}
    .brand{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:30px;color:#e9b83f;letter-spacing:2px}
    .ey{font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.3em;font-size:12px;color:#9a9aa7;margin:18px 0 4px}
    h1{font-family:'Big Shoulders Display',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,6vw,50px);line-height:.95;color:#f4efe3;margin-bottom:18px}
    .who{font-size:26px;font-family:'Big Shoulders Display',sans-serif;color:#e9b83f;text-transform:uppercase;margin:8px 0}
    p{color:#cfc9bd;font-size:15px;line-height:1.7;max-width:560px;margin:8px auto}
    .val{display:inline-block;margin:22px 0;background:rgba(233,184,63,.12);border:1px solid #e9b83f;border-radius:12px;padding:14px 26px}
    .val .n{font-family:'Big Shoulders Display',sans-serif;font-weight:900;font-size:32px;color:#e9b83f;line-height:1}
    .val .l{font-size:11px;color:#9a9aa7;text-transform:uppercase;letter-spacing:.08em}
    .foot{display:flex;justify-content:space-around;margin-top:34px;font-size:13px;color:#9a9aa7}
    .foot b{color:#f4efe3;display:block;font-family:'Big Shoulders Display',sans-serif;font-size:16px}
    .btn{display:inline-block;background:#e9b83f;color:#1a1405;text-decoration:none;font-weight:700;padding:12px 22px;border-radius:8px;border:0;cursor:pointer;margin:0 auto 18px}
    .noprint{text-align:center}
    @media print{body{background:#fff;padding:0}.noprint{display:none}}
</style>
</head>
<body>
    <div class="noprint"><button class="btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button></div>
    <div class="cert">
        <div class="brand">{{ strtoupper($brand) }}</div>
        <div class="ey">Certificado de entrega</div>
        <h1>Promoción<br>cumplida 🎓</h1>
        <p>{{ $brand }} certifica que</p>
        <div class="who">{{ $school->name }}</div>
        <p>completó satisfactoriamente su contrato <b>{{ $contract->code }}</b> de chaquetas de promoción para el grado once {{ $contract->year }}, con <b>{{ $contract->students->count() }}</b> estudiantes.</p>
        <div class="val">
            <div class="n">{{ $money($contract->totalValue()) }}</div>
            <div class="l">Valor total del contrato</div>
        </div>
        <p style="font-size:13px">Presenta este certificado en tu próxima contratación y recibe atención preferencial. ¡Gracias por confiar en nosotros! 💛</p>
        <div class="foot">
            <div><b>{{ optional($contract->signed_at)->format('d/m/Y') ?: now()->format('d/m/Y') }}</b>Fecha</div>
            <div><b>{{ $contract->jacketType?->name ?? 'Chaqueta' }}</b>Prenda</div>
            <div><b>{{ $brand }}</b>Fabricante</div>
        </div>
    </div>
</body>
</html>

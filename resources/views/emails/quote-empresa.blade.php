@php
    $first = \Illuminate\Support\Str::of($quote->customer_name)->explode(' ')->first();
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $img = $baseUrl.'/img/cotizacion';
    $lines = $quote->businessLines();
    $totals = $quote->businessTotals();
    $font = "font-family:'Montserrat',Arial,Helvetica,sans-serif;";
    $display = "font-family:'Anton','Arial Black',Impact,Arial,sans-serif;font-weight:400;text-transform:uppercase;letter-spacing:.5px;";
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<title>Cotización {{ $quote->code() }} · {{ $quote->school }}</title>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
<style>
  @media only screen and (max-width:620px){
    .w100{width:100%!important;display:block!important}
    .px{padding-left:20px!important;padding-right:20px!important}
    .h1{font-size:36px!important}
    .stack td{display:block!important;width:100%!important;box-sizing:border-box}
    .stack td.sp{height:10px!important}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#0E0E0E;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $first }}, la cotización {{ $quote->code() }} para {{ $quote->school }} está lista: {{ count($lines) }} prenda(s), {{ $totals['units'] }} unidades.</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0E0E0E;">
<tr><td align="center" style="padding:24px 10px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" class="w100" style="width:600px;max-width:600px;">

    <tr><td style="padding:0 0 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
            <td style="vertical-align:middle;"><img src="{{ $img }}/logo-light.png" width="150" alt="{{ $brand }}" style="display:block;width:150px;height:auto;border:0;"></td>
            <td align="right" style="vertical-align:middle;">
                <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#FFC72C;padding:8px 14px;border-radius:8px;{{ $font }}">
                    <div style="font-size:9px;font-weight:800;letter-spacing:2px;color:#0E0E0E;">COTIZACIÓN N°</div>
                    <div style="{{ $display }}font-size:24px;line-height:1;color:#0E0E0E;">{{ $quote->number }}</div>
                </td></tr></table>
            </td>
        </tr></table>
    </td></tr>

    <tr><td class="px" style="background:#FFC72C;padding:30px 36px 28px;border-radius:18px 18px 0 0;{{ $font }}">
        <div style="font-size:12px;font-weight:800;letter-spacing:4px;color:#0E0E0E;">COTIZACIÓN EMPRESARIAL</div>
        <div class="h1" style="{{ $display }}font-size:44px;line-height:1;color:#0E0E0E;margin:8px 0 12px;">¡Hola, {{ $first }}!</div>
        <div style="font-size:15px;line-height:1.6;color:#1A1A1A;">
            Gracias por pensar en G'Lauren para las prendas de <b>{{ $quote->school }}</b>.
            Preparamos tu cotización con cada prenda, sus características y el valor por unidad al por mayor.
        </div>
    </td></tr>

    <tr><td class="px" style="background:#FFFFFF;padding:26px 36px 10px;{{ $font }}">
        @foreach($lines as $l)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px solid #0E0E0E;border-radius:14px;margin-bottom:12px;border-collapse:separate;"><tr>
                @if($l['photo_url'])
                    <td width="110" valign="middle" style="padding:10px;"><img src="{{ $l['photo_url'] }}" width="100" alt="{{ $l['name'] }}" style="display:block;width:100px;height:auto;border-radius:10px;border:0;"></td>
                @endif
                <td valign="middle" style="padding:12px 14px;">
                    <div style="{{ $display }}font-size:19px;color:#0E0E0E;line-height:1.1;">{{ $l['name'] }}</div>
                    @if($l['features'])<div style="font-size:12px;color:#555555;line-height:1.5;margin-top:4px;">{{ implode(' · ', array_slice($l['features'], 0, 4)) }}</div>@endif
                    <div style="font-size:13px;color:#1A1A1A;margin-top:6px;"><b>{{ $l['qty'] }}</b> und. × <b>{{ $money($l['unit_price']) }}</b> = <b style="color:#0E0E0E;">{{ $money($l['subtotal']) }}</b></div>
                </td>
            </tr></table>
        @endforeach
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0E0E0E;border-radius:14px;"><tr>
            <td style="padding:16px 18px;font-size:14px;font-weight:800;color:#FFFFFF;">Total · {{ $totals['units'] }} unidades</td>
            <td align="right" style="padding:16px 18px;{{ $display }}font-size:28px;color:#FFC72C;white-space:nowrap;">{{ $money($totals['total']) }}</td>
        </tr></table>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;background:#FFF4D6;border:2px solid #FFC72C;border-radius:14px;"><tr><td style="padding:14px 18px;">
            <div style="{{ $display }}font-size:18px;color:#0E0E0E;">🎁 Obsequios incluidos</div>
            <div style="font-size:13px;line-height:1.7;color:#1A1A1A;font-weight:700;">
                ✓ Tula de obsequio<br>
                ✓ 1 chaqueta adicional de obsequio por cada 25 unidades
                @if($totals['gift_jackets'] > 0) — <span style="color:#C2410C;">tu pedido recibe {{ $totals['gift_jackets'] }}</span>@endif
            </div>
        </td></tr></table>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;"><tr>
            <td align="center" style="background:#0E0E0E;border-radius:999px;box-shadow:0 6px 0 #FF6B1A;">
                <a href="{{ $url }}" style="display:block;padding:18px 24px;{{ $display }}font-size:22px;color:#FFC72C;text-decoration:none;">Ver la cotización completa →</a>
            </td>
        </tr></table>
        <div style="text-align:center;font-size:12px;color:#555555;margin:12px 0 22px;">Fotos, características, valores al por mayor, obsequios y condiciones · Puedes descargarla en PDF</div>
    </td></tr>

    <tr><td class="px" align="center" style="background:#1A1A1A;padding:26px 36px;border-radius:0 0 18px 18px;{{ $font }}">
        <div style="font-size:12px;line-height:1.7;color:#D6D6D6;">Entrega en 25–35 días hábiles · Pago 50/50 · Garantía por defectos de fabricación · Telas Lafayette y Patprimo</div>
        @if($waUrl)
        <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin-top:16px;"><tr>
            <td align="center" style="background:#25D366;border-radius:999px;">
                <a href="{{ $waUrl }}" style="display:block;padding:14px 30px;font-size:15px;font-weight:800;color:#04240F;text-decoration:none;">Hablar por WhatsApp</a>
            </td>
        </tr></table>
        @endif
    </td></tr>

    <tr><td align="center" style="padding:22px 10px;{{ $font }}">
        <div style="font-size:12px;line-height:1.7;color:#8A8A92;">
            <b style="color:#FFFFFF;">G'LAUREN</b> · +10 años confeccionando prendas personalizadas<br>
            Calle 48K 5A-27 sur, Bogotá · <a href="{{ $baseUrl }}" style="color:#FFC72C;text-decoration:none;">{{ str_replace(['https://','http://'],'',$baseUrl) }}</a><br>
            Cotización {{ $quote->code() }} · No válida como factura de compra
        </div>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>

@php
    $first = \Illuminate\Support\Str::of($quote->customer_name)->explode(' ')->first();
    $cfg = config('cotizacion');
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $img = $baseUrl.'/img/cotizacion';
    $font = "font-family:'Montserrat',Arial,Helvetica,sans-serif;";
    $display = "font-family:'Anton','Arial Black',Impact,Arial,sans-serif;font-weight:400;text-transform:uppercase;letter-spacing:.5px;";
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<title>Tu cotización {{ $quote->code() }}</title>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
<style>
  @media only screen and (max-width:620px){
    .w100{width:100%!important;display:block!important}
    .px{padding-left:20px!important;padding-right:20px!important}
    .h1{font-size:38px!important}
    .stack td{display:block!important;width:100%!important;box-sizing:border-box}
    .stack td.sp{height:10px!important}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#0E0E0E;">
{{-- Texto de vista previa en la bandeja de entrada --}}
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $first }}, tu cotización {{ $quote->code() }} de chaquetas de prom está lista. Mira precios, combos con descuento y todo lo que incluye tu promoción.</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0E0E0E;">
<tr><td align="center" style="padding:24px 10px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" class="w100" style="width:600px;max-width:600px;">

    {{-- Encabezado --}}
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

    {{-- Imagen principal --}}
    <tr><td style="border-radius:18px 18px 0 0;overflow:hidden;line-height:0;">
        <a href="{{ $url }}"><img src="{{ $img }}/email-hero.jpg" width="600" alt="Chaquetas y busos de prom G'Lauren" style="display:block;width:100%;max-width:600px;height:auto;border:0;border-radius:18px 18px 0 0;"></a>
    </td></tr>

    {{-- Saludo --}}
    <tr><td class="px" style="background:#FFC72C;padding:30px 36px 28px;{{ $font }}">
        <div style="font-size:12px;font-weight:800;letter-spacing:4px;color:#0E0E0E;">TU COTIZACIÓN ESTÁ LISTA</div>
        <div class="h1" style="{{ $display }}font-size:48px;line-height:1;color:#0E0E0E;margin:8px 0 12px;">¡Hola, {{ $first }}!</div>
        <div style="font-size:15px;line-height:1.6;color:#1A1A1A;">
            Gracias por confiar en G'Lauren para la promoción{{ $quote->school ? ' de '.$quote->school : '' }}.
            Preparamos tu cotización con precios, combos con descuento y todo lo que incluye tu prom.
        </div>
    </td></tr>

    {{-- Tarjeta blanca --}}
    <tr><td class="px" style="background:#FFFFFF;padding:28px 36px 8px;{{ $font }}">

        {{-- Datos --}}
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="stack" style="margin-bottom:22px;"><tr>
            <td width="33%" style="padding:12px 14px;background:#0E0E0E;border-radius:12px;">
                <div style="font-size:10px;font-weight:800;letter-spacing:2px;color:#FFC72C;">PARA</div>
                <div style="font-size:14px;font-weight:700;color:#FFFFFF;">{{ $quote->customer_name }}</div>
            </td>
            <td class="sp" width="8" style="width:8px;"></td>
            <td width="33%" style="padding:12px 14px;background:#0E0E0E;border-radius:12px;">
                <div style="font-size:10px;font-weight:800;letter-spacing:2px;color:#FFC72C;">ESTUDIANTES</div>
                <div style="font-size:14px;font-weight:700;color:#FFFFFF;">{{ $quote->quantity }}</div>
            </td>
            <td class="sp" width="8" style="width:8px;"></td>
            <td width="33%" style="padding:12px 14px;background:#0E0E0E;border-radius:12px;">
                <div style="font-size:10px;font-weight:800;letter-spacing:2px;color:#FFC72C;">FECHA</div>
                <div style="font-size:14px;font-weight:700;color:#FFFFFF;">{{ $quote->created_at->format('d/m/Y') }}</div>
            </td>
        </tr></table>

        {{-- Lo que cotizaste --}}
        @if(count($summary['lines']))
            <div style="{{ $display }}font-size:24px;color:#0E0E0E;margin-bottom:10px;">Lo que cotizaste</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px solid #0E0E0E;border-radius:14px;border-collapse:separate;overflow:hidden;">
                @foreach($summary['lines'] as $l)
                    <tr>
                        <td style="padding:12px 16px;font-size:14px;color:#1A1A1A;border-bottom:1px solid #EEEEEE;">{{ $l['label'] }}</td>
                        <td align="right" style="padding:12px 16px;{{ $display }}font-size:20px;color:#0E0E0E;white-space:nowrap;border-bottom:1px solid #EEEEEE;">{{ $money($l['price']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="padding:14px 16px;background:#0E0E0E;font-size:14px;font-weight:800;color:#FFFFFF;">Valor por estudiante</td>
                    <td align="right" style="padding:14px 16px;background:#0E0E0E;{{ $display }}font-size:26px;color:#FFC72C;white-space:nowrap;">{{ $money($summary['unit_total']) }}</td>
                </tr>
                @if($quote->quantity > 1)
                <tr>
                    <td style="padding:12px 16px;background:#1A1A1A;font-size:13px;font-weight:700;color:#D6D6D6;">Total aproximado · {{ $quote->quantity }} estudiantes</td>
                    <td align="right" style="padding:12px 16px;background:#1A1A1A;{{ $display }}font-size:20px;color:#FFFFFF;white-space:nowrap;">{{ $money($summary['unit_total'] * $quote->quantity) }}</td>
                </tr>
                @endif
            </table>
        @elseif((float) $quote->estimated_price > 0)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0E0E0E;border-radius:14px;"><tr>
                <td style="padding:16px 18px;font-size:14px;font-weight:700;color:#FFFFFF;">Valor estimado de tu diseño<br><span style="font-weight:400;color:#BDBDBD;font-size:12px;">{{ $quote->quantity }} {{ $quote->quantity == 1 ? 'unidad' : 'unidades' }}</span></td>
                <td align="right" style="padding:16px 18px;{{ $display }}font-size:28px;color:#FFC72C;white-space:nowrap;">{{ $money($quote->estimated_price) }}</td>
            </tr></table>
        @endif

        {{-- Botón principal --}}
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;"><tr>
            <td align="center" style="background:#0E0E0E;border-radius:999px;box-shadow:0 6px 0 #FF6B1A;">
                <a href="{{ $url }}" style="display:block;padding:18px 24px;{{ $display }}font-size:22px;color:#FFC72C;text-decoration:none;">Ver mi cotización completa →</a>
            </td>
        </tr></table>
        <div style="text-align:center;font-size:12px;color:#555555;margin:12px 0 22px;">Precios, combos, adicionales y paquete de fotografía · Puedes descargarla en PDF</div>
    </td></tr>

    {{-- Beneficios --}}
    <tr><td class="px" style="background:#0E0E0E;padding:30px 36px;{{ $font }}">
        <div style="font-size:11px;font-weight:800;letter-spacing:4px;color:#FFC72C;">POR QUÉ G'LAUREN</div>
        <div style="{{ $display }}font-size:34px;color:#FFFFFF;margin:6px 0 16px;">Tus <span style="color:#FFC72C;">beneficios</span></div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="stack"><tr>
            @foreach($cfg['benefits'] as $i => $b)
                @if($i > 0)<td class="sp" width="10" style="width:10px;"></td>@endif
                <td width="33%" valign="top" style="background:{{ ['#FFC72C','#FF6B1A','#FFFFFF'][$i % 3] }};border-radius:14px;padding:16px 14px;">
                    <div style="{{ $display }}font-size:40px;line-height:1;color:#0E0E0E;">{{ $b['big'] }}</div>
                    <div style="{{ $display }}font-size:14px;color:#0E0E0E;margin-top:4px;">{{ $b['title'] }}</div>
                    <div style="font-size:12px;line-height:1.4;font-weight:600;color:#1A1A1A;margin-top:4px;">{{ $b['text'] }}</div>
                </td>
            @endforeach
        </tr></table>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;background:#FFC72C;border-radius:14px;"><tr><td style="padding:16px 18px;">
            <div style="{{ $display }}font-size:20px;color:#0E0E0E;margin-bottom:8px;">Incluido en tu prom</div>
            <div style="font-size:14px;font-weight:800;color:#0E0E0E;line-height:1.9;">
                @foreach($cfg['included'] as $x)<span style="display:inline-block;background:#FFFFFF;border-radius:999px;padding:4px 12px;margin:0 4px 6px 0;">✓ {{ $x }}</span>@endforeach
            </div>
        </td></tr></table>

        <div style="font-size:12px;line-height:1.7;color:#BDBDBD;margin-top:14px;">
            Entrega en 25–35 días hábiles · Pago 50/50 · Garantía todo el año escolar · Telas Lafayette y Patprimo · Cremalleras YES
        </div>
    </td></tr>

    {{-- Paquete de fotografía --}}
    <tr><td class="px" style="background:#1A1A1A;padding:24px 36px;{{ $font }}border-top:4px solid #FF6B1A;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="stack"><tr>
            <td valign="middle">
                <div style="font-size:11px;font-weight:800;letter-spacing:3px;color:#FF6B1A;">¡PROMOCIÓN!</div>
                <div style="{{ $display }}font-size:24px;color:#FFFFFF;margin:4px 0;">Paquete de fotografía de grado</div>
                <div style="font-size:12px;color:#D6D6D6;line-height:1.5;">Sesión, carpeta de cuero, diploma, estola, fotos digitales e impresas y anuario. {{ $cfg['photo_package']['benefit'] }}</div>
            </td>
            <td class="sp" width="14" style="width:14px;"></td>
            <td width="170" align="center" valign="middle" style="background:#FFC72C;border-radius:14px;padding:12px;">
                <div style="font-size:11px;font-weight:800;color:#0E0E0E;">ANTES <s>{{ $money($cfg['photo_package']['before']) }}</s></div>
                <div style="{{ $display }}font-size:32px;color:#0E0E0E;line-height:1.1;">{{ $money($cfg['photo_package']['price']) }}</div>
                <div style="font-size:10px;font-weight:700;color:#0E0E0E;">por estudiante</div>
            </td>
        </tr></table>
    </td></tr>

    {{-- Cierre --}}
    <tr><td class="px" align="center" style="background:#FFFFFF;padding:30px 36px;border-radius:0 0 18px 18px;{{ $font }}">
        <div style="{{ $display }}font-size:30px;color:#0E0E0E;">¿Listos para su prom?</div>
        <div style="font-size:14px;color:#333333;margin:8px 0 18px;">Escríbenos y separamos la fecha de toma de medidas para tu colegio.</div>
        @if($waUrl)
        <table role="presentation" cellpadding="0" cellspacing="0" align="center"><tr>
            <td align="center" style="background:#25D366;border-radius:999px;">
                <a href="{{ $waUrl }}" style="display:block;padding:14px 30px;font-size:15px;font-weight:800;color:#04240F;text-decoration:none;">Hablar por WhatsApp</a>
            </td>
        </tr></table>
        @endif
        <div style="font-size:12px;color:#777777;margin-top:16px;">También puedes responder este correo.</div>
    </td></tr>

    {{-- Pie --}}
    <tr><td align="center" style="padding:22px 10px;{{ $font }}">
        <div style="font-size:12px;line-height:1.7;color:#8A8A92;">
            <b style="color:#FFFFFF;">G'LAUREN</b> · +10 años vistiendo promociones · +25 colegios<br>
            Calle 48K 5A-27 sur, Bogotá · <a href="{{ $baseUrl }}" style="color:#FFC72C;text-decoration:none;">{{ str_replace(['https://','http://'],'',$baseUrl) }}</a><br>
            Cotización {{ $quote->code() }} · No válida como factura de compra
        </div>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>

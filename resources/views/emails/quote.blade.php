@php $first = \Illuminate\Support\Str::of($quote->customer_name)->explode(' ')->first(); @endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light dark"></head>
<body style="margin:0;padding:0;background:#0E0E0E;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0E0E0E;padding:24px 12px;font-family:Arial,Helvetica,sans-serif;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

    <tr><td style="padding:6px 0 18px;text-align:center;">
        <img src="{{ $baseUrl }}/img/cotizacion/logo-light.png" alt="{{ $brand }}" width="170" style="display:inline-block;width:170px;height:auto;border:0;">
    </td></tr>

    <tr><td style="background:#FFFFFF;border-radius:16px;overflow:hidden;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr><td style="height:8px;background:#FF6B1A;"></td></tr>
            <tr><td style="background:#FFC72C;padding:22px 30px;">
                <p style="margin:0;color:#0E0E0E;font-size:12px;letter-spacing:3px;font-weight:bold;">COTIZACIÓN N°</p>
                <p style="margin:2px 0 0;color:#0E0E0E;font-size:34px;font-weight:900;">{{ $quote->number }}</p>
            </td></tr>
            <tr><td style="padding:28px 30px 6px;">
                <h1 style="margin:0 0 12px;color:#0E0E0E;font-size:24px;line-height:1.25;">¡Hola, {{ $first }}! Aquí está tu cotización 🧥</h1>
                <p style="margin:0;color:#333333;font-size:15px;line-height:1.6;">
                    Gracias por diseñar tu prenda con nosotros{{ $quote->school ? ' para '.$quote->school : '' }}.
                    En el enlace encuentras precios, combos con descuento, beneficios y todo lo que incluye tu prom.
                </p>
            </td></tr>

            @if(count($summary['lines']))
            <tr><td style="padding:16px 30px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px solid #0E0E0E;border-radius:12px;">
                    @foreach($summary['lines'] as $l)
                        <tr><td style="padding:10px 14px;font-size:14px;color:#0E0E0E;border-bottom:1px solid #eeeeee;">{{ $l['label'] }}</td>
                            <td style="padding:10px 14px;font-size:15px;font-weight:bold;color:#0E0E0E;text-align:right;white-space:nowrap;border-bottom:1px solid #eeeeee;">${{ number_format($l['price'], 0, ',', '.') }}</td></tr>
                    @endforeach
                    <tr><td style="padding:12px 14px;font-size:14px;font-weight:bold;background:#0E0E0E;color:#FFFFFF;">Valor por estudiante</td>
                        <td style="padding:12px 14px;font-size:18px;font-weight:900;background:#0E0E0E;color:#FFC72C;text-align:right;white-space:nowrap;">${{ number_format($summary['unit_total'], 0, ',', '.') }}</td></tr>
                </table>
            </td></tr>
            @elseif((float) $quote->estimated_price > 0)
            <tr><td style="padding:16px 30px 0;">
                <p style="margin:0;font-size:15px;color:#0E0E0E;">Valor estimado de tu diseño ({{ $quote->quantity }} {{ $quote->quantity == 1 ? 'unidad' : 'unidades' }}):
                    <b style="font-size:18px;">${{ number_format($quote->estimated_price, 0, ',', '.') }}</b></p>
            </td></tr>
            @endif

            <tr><td style="padding:24px 30px 8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr><td align="center" style="background:#0E0E0E;border-radius:999px;">
                        <a href="{{ $url }}" style="display:block;padding:15px 24px;color:#FFC72C;font-size:16px;font-weight:bold;text-decoration:none;">Ver mi cotización completa →</a>
                    </td></tr>
                </table>
            </td></tr>
            @if($waUrl)
            <tr><td style="padding:6px 30px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr><td align="center" style="background:#25D366;border-radius:999px;">
                        <a href="{{ $waUrl }}" style="display:block;padding:13px 24px;color:#04240F;font-size:15px;font-weight:bold;text-decoration:none;">Escríbenos por WhatsApp</a>
                    </td></tr>
                </table>
            </td></tr>
            @endif

            <tr><td style="padding:22px 30px 28px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF4CC;border-radius:12px;">
                    <tr><td style="padding:14px 16px;font-size:13px;line-height:1.6;color:#0E0E0E;">
                        <b>Incluido en tu prom:</b> bandera, globos, fotografía y video en la entrega, tula o termo.<br>
                        <b>Beneficios:</b> 10% hermanos o primos · 3% al combinar prendas · 1 chaqueta gratis por cada 25.
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </td></tr>

    <tr><td style="padding:20px 10px;text-align:center;">
        <p style="margin:0;color:#8a8a92;font-size:12px;line-height:1.6;">{{ $brand }} · Chaquetas y busos de promoción · Colombia<br>
        <a href="{{ $baseUrl }}" style="color:#FFC72C;text-decoration:none;">{{ str_replace(['https://','http://'],'',$baseUrl) }}</a> · Cotización no válida como factura de compra</p>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light dark"></head>
<body style="margin:0;padding:0;background:#0d0d0f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0f;padding:24px 12px;font-family:Arial,Helvetica,sans-serif;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                {{-- Encabezado --}}
                <tr><td style="padding:8px 0 20px;text-align:center;">
                    <span style="display:inline-block;background:#e9b83f;color:#1a1405;font-weight:900;font-size:22px;letter-spacing:1px;padding:10px 20px;border-radius:10px;font-family:Arial,Helvetica,sans-serif;">{{ strtoupper($brand) }}</span>
                </td></tr>

                {{-- Tarjeta principal --}}
                <tr><td style="background:#15151b;border:1px solid #2a2a33;border-radius:16px;overflow:hidden;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr><td style="height:5px;background:#e9b83f;"></td></tr>
                        <tr><td style="padding:34px 34px 8px;">
                            <p style="margin:0 0 4px;color:#e9b83f;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;">Gracias por escribirnos</p>
                            <h1 style="margin:0 0 18px;color:#f4efe3;font-size:26px;line-height:1.2;">¡Hola, {{ $name }}! 👋</h1>
                            <div style="color:#c7c3b8;font-size:15px;line-height:1.7;">{!! nl2br(e($body)) !!}</div>
                        </td></tr>

                        @if($appt)
                        <tr><td style="padding:6px 34px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#12251a;border:1px solid #2f5f3f;border-radius:12px;">
                                <tr><td style="padding:16px 18px;">
                                    <p style="margin:0 0 4px;color:#6fdc8c;font-size:13px;font-weight:bold;">✅ Tu reunión quedó agendada</p>
                                    <p style="margin:0;color:#f4efe3;font-size:17px;font-weight:bold;">📅 {{ $appt->date->locale('es')->isoFormat('dddd D [de] MMMM') }} · 🕐 {{ substr($appt->time,0,5) }}</p>
                                </td></tr>
                            </table>
                        </td></tr>
                        @endif

                        {{-- Botón --}}
                        <tr><td style="padding:26px 34px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                <tr><td align="center" style="background:#e9b83f;border-radius:10px;">
                                    <a href="{{ $baseUrl }}/personalizar" style="display:block;padding:15px 24px;color:#1a1405;font-size:16px;font-weight:bold;text-decoration:none;">Diseña la tuya en 3D →</a>
                                </td></tr>
                            </table>
                        </td></tr>

                        {{-- Ayuda --}}
                        <tr><td style="padding:8px 34px 34px;text-align:center;">
                            <p style="margin:14px 0 0;color:#8a8a92;font-size:13px;">¿Dudas? Responde este correo o escríbenos por WhatsApp. Estamos para ayudarte 💛</p>
                        </td></tr>
                    </table>
                </td></tr>

                {{-- Pie --}}
                <tr><td style="padding:22px 10px;text-align:center;">
                    <p style="margin:0;color:#6b6b73;font-size:12px;line-height:1.6;">{{ $brand }} · Chaquetas y busos de promoción · Colombia<br>
                    <a href="{{ $baseUrl }}" style="color:#e9b83f;text-decoration:none;">{{ str_replace(['https://','http://'],'',$baseUrl) }}</a></p>
                </td></tr>

            </table>
        </td></tr>
    </table>
</body>
</html>

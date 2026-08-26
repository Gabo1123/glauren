<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#eef0f3;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef0f3;padding:24px 12px;font-family:Arial,Helvetica,sans-serif;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06);">

                {{-- Encabezado --}}
                <tr><td style="background:#0d0d0f;padding:20px 28px;">
                    <table role="presentation" width="100%"><tr>
                        <td style="color:#e9b83f;font-weight:900;font-size:18px;letter-spacing:1px;">GLAUREN</td>
                        <td align="right" style="color:#8a8a92;font-size:12px;">{{ $appt ? 'Nueva cita' : 'Nuevo contacto' }}</td>
                    </tr></table>
                </td></tr>

                <tr><td style="padding:26px 28px 6px;">
                    <h1 style="margin:0 0 4px;color:#15151b;font-size:22px;">
                        @if($appt)📅 Nueva cita agendada @else🔔 Nuevo contacto @endif
                    </h1>
                    <p style="margin:0;color:#6b7280;font-size:14px;">Llegó desde el sitio web.</p>
                </td></tr>

                @if($appt)
                <tr><td style="padding:16px 28px 0;">
                    <table role="presentation" width="100%" style="background:#eff9f0;border:1px solid #cdeccd;border-radius:10px;">
                        <tr><td style="padding:14px 16px;color:#166534;font-size:15px;">
                            <b>{{ $appt->date->locale('es')->isoFormat('dddd D [de] MMMM') }}</b> a las <b>{{ substr($appt->time,0,5) }}</b>
                        </td></tr>
                    </table>
                </td></tr>
                @endif

                {{-- Datos --}}
                <tr><td style="padding:20px 28px 8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                        @php $row = fn($k,$v)=> $v ? '<tr><td style="padding:9px 0;color:#9ca3af;width:120px;border-bottom:1px solid #f0f0f2;">'.$k.'</td><td style="padding:9px 0;color:#15151b;font-weight:bold;border-bottom:1px solid #f0f0f2;">'.$v.'</td></tr>' : ''; @endphp
                        {!! $row('Nombre', e($lead->name)) !!}
                        {!! $row('Teléfono', $lead->phone ? '<a href="https://wa.me/'.preg_replace('/\D/','',$lead->phone).'" style="color:#16a34a;text-decoration:none;">'.e($lead->phone).'</a>' : '') !!}
                        {!! $row('Correo', $lead->email ? '<a href="mailto:'.e($lead->email).'" style="color:#2563eb;text-decoration:none;">'.e($lead->email).'</a>' : '') !!}
                        {!! $row('Colegio', e($lead->school)) !!}
                        {!! $row('Mensaje', e($lead->message)) !!}
                        {!! $row('Origen', e($lead->source)) !!}
                    </table>
                </td></tr>

                {{-- Botón responder --}}
                @if($lead->phone)
                <tr><td style="padding:14px 28px 28px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;"><tr>
                        <td align="center" style="background:#25d366;border-radius:10px;">
                            <a href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}" style="display:block;padding:13px 22px;color:#04240f;font-size:15px;font-weight:bold;text-decoration:none;">💬 Responder por WhatsApp</a>
                        </td>
                    </tr></table>
                </td></tr>
                @endif

            </table>
            <p style="margin:16px 0 0;color:#9ca3af;font-size:12px;">Glauren · aviso automático del sitio</p>
        </td></tr>
    </table>
</body>
</html>

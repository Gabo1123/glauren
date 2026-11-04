<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light dark"></head>
<body style="margin:0;padding:0;background:#0d0d0f;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0f;padding:24px 12px;font-family:Arial,Helvetica,sans-serif;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
    <tr><td style="padding:8px 0 20px;text-align:center;">
        <span style="display:inline-block;background:#e9b83f;color:#1a1405;font-weight:900;font-size:22px;letter-spacing:1px;padding:10px 20px;border-radius:10px;">G'LAUREN</span>
    </td></tr>
    <tr><td style="background:#15151b;border:1px solid #2a2a33;border-radius:16px;overflow:hidden;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr><td style="height:5px;background:#e9b83f;"></td></tr>
            <tr><td style="padding:30px 30px 10px;">
                @if($kicker)<p style="margin:0 0 4px;color:#e9b83f;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;">{{ $kicker }}</p>@endif
                <h1 style="margin:0 0 14px;color:#f4efe3;font-size:24px;line-height:1.25;">{{ $heading }}</h1>
                <div style="color:#c7c3b8;font-size:15px;line-height:1.7;">{!! nl2br(e($body)) !!}</div>
            </td></tr>
            @foreach($rows as $r)
                <tr><td style="padding:6px 30px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#1d1d25;border:1px solid #2a2a33;border-radius:10px;">
                        <tr><td style="padding:12px 14px;">
                            @if(!empty($r['tag']))<span style="float:right;background:#2b2412;color:#e9b83f;font-size:11px;font-weight:bold;padding:3px 8px;border-radius:20px;">{{ $r['tag'] }}</span>@endif
                            <div style="color:#f4efe3;font-size:15px;font-weight:bold;">{{ $r['title'] }}</div>
                            @if(!empty($r['sub']))<div style="color:#9a968c;font-size:13px;margin-top:3px;">{{ $r['sub'] }}</div>@endif
                            @if(!empty($r['url']))<div style="margin-top:8px;"><a href="{{ $r['url'] }}" style="color:#e9b83f;font-size:13px;font-weight:bold;text-decoration:none;">{{ $r['link'] ?? 'Abrir →' }}</a></div>@endif
                        </td></tr>
                    </table>
                </td></tr>
            @endforeach
            @if($buttonText && $buttonUrl)
                <tr><td style="padding:20px 30px 30px;" align="center">
                    <a href="{{ $buttonUrl }}" style="display:inline-block;background:#e9b83f;color:#1a1405;font-weight:bold;font-size:15px;padding:13px 26px;border-radius:10px;text-decoration:none;">{{ $buttonText }}</a>
                </td></tr>
            @else
                <tr><td style="height:24px"></td></tr>
            @endif
        </table>
    </td></tr>
    <tr><td style="padding:16px;text-align:center;color:#6b675e;font-size:12px;">chaquetasdeprom.com</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

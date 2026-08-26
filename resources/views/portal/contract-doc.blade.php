@php use App\Modules\Chaquetas\Models\Setting; $brand = Setting::get('brand_name','Glauren'); $money = fn($v)=>'$'.number_format((float)$v,0,',','.'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contrato {{ $contract->code }} · {{ $brand }}</title><meta name="robots" content="noindex">
<style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Georgia,'Times New Roman',serif;color:#1a1a1a;background:#eef0f3;padding:24px;line-height:1.6}
    .sheet{max-width:800px;margin:0 auto;background:#fff;padding:48px 54px;box-shadow:0 6px 30px rgba(0,0,0,.1)}
    .top{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #e9b83f;padding-bottom:16px;margin-bottom:26px}
    .brand{font-family:Arial,sans-serif;font-weight:900;font-size:26px;color:#111;letter-spacing:1px}
    .brand span{color:#e9b83f}
    h1{font-size:22px;margin-bottom:4px}
    .muted{color:#666;font-size:14px}
    h2{font-size:15px;text-transform:uppercase;letter-spacing:.05em;margin:24px 0 8px;color:#b58a1f;border-bottom:1px solid #eee;padding-bottom:4px}
    table{width:100%;border-collapse:collapse;font-size:14px;margin:6px 0}
    td{padding:6px 4px;border-bottom:1px solid #f0f0f0}
    td.k{color:#666;width:200px}
    .terms{font-size:13.5px;white-space:pre-wrap;color:#333}
    .signers{display:flex;flex-wrap:wrap;gap:24px;margin-top:20px}
    .sig{border-top:1px solid #333;padding-top:6px;min-width:200px;text-align:center;font-size:13px}
    .sig img{max-height:60px;display:block;margin:0 auto 4px}
    .btn{display:inline-block;background:#e9b83f;color:#1a1405;text-decoration:none;font-family:Arial,sans-serif;font-weight:700;padding:12px 22px;border-radius:8px;border:0;cursor:pointer;margin:0 auto 18px}
    .noprint{text-align:center}
    @media print{body{background:#fff;padding:0}.sheet{box-shadow:none;max-width:none}.noprint{display:none}}
</style>
</head>
<body>
    <div class="noprint"><button class="btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button></div>
    <div class="sheet">
        <div class="top">
            <div class="brand">{{ strtoupper($brand) }}</div>
            <div style="text-align:right"><div class="muted">Contrato</div><b>{{ $contract->code }}</b></div>
        </div>

        <div class="terms" style="margin-top:20px">{{ $contract->renderedContract() }}</div>

        <h2>Firmas</h2>
        <div class="signers">
            @forelse($contract->signers as $s)
                <div class="sig">
                    @if($s->signatureUrl())<img src="{{ $s->signatureUrl() }}" alt="firma">@endif
                    {{ $s->name }}@if($s->role) · {{ $s->role }}@endif
                    @if($s->signed_document)<br><span class="muted">CC {{ $s->signed_document }}</span>@endif
                    @if($s->signed_at)<br><span class="muted">{{ $s->signed_at->format('d/m/Y') }}</span>@endif
                </div>
            @empty
                <div class="sig">{{ $contract->signed_by_name ?: 'Pendiente de firma' }}</div>
            @endforelse
        </div>
    </div>
</body>
</html>

@php $cover = collect($lines)->firstWhere('photo_url'); @endphp
<div class="pg dark">
    <div aria-hidden="true" style="position:absolute;inset:0;background:#FFC72C;clip-path:polygon(64% 0,100% 0,100% 100%,36% 100%)"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background:#FF6B1A;clip-path:polygon(57% 0,61.5% 0,33.5% 100%,29% 100%)"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background:#FF6B1A;clip-path:polygon(52.5% 0,54% 0,26% 100%,24.5% 100%)"></div>

    @if($cover)
        <div style="position:absolute;left:432px;top:250px;width:318px;height:400px;background:#fff;border:4px solid #0E0E0E;border-radius:24px;box-shadow:12px 12px 0 #FF6B1A;transform:rotate(3deg);overflow:hidden;display:flex;align-items:center;justify-content:center">
            <img src="{{ $cover['photo_url'] }}" alt="{{ $cover['name'] }}" style="width:100%;height:100%;object-fit:contain">
        </div>
        <div style="position:absolute;left:470px;top:676px;transform:rotate(3deg);background:#0E0E0E;color:#FFC72C;font-weight:800;font-size:12px;letter-spacing:2px;padding:7px 14px;border-radius:999px;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ mb_strtoupper($cover['name']) }}</div>
    @else
        <div style="position:absolute;left:428px;top:280px;width:330px;height:370px;border:4px solid #0E0E0E;border-radius:24px;box-shadow:12px 12px 0 #FF6B1A;transform:rotate(3deg);overflow:hidden">
            <img src="/img/cotizacion/empresas/equipo-1.jpg" alt="Equipo con chaquetas corporativas" style="width:100%;height:100%;object-fit:cover;object-position:60% 30%">
        </div>
    @endif

    <img src="/img/cotizacion/logo-light.png" alt="G'Lauren" style="position:absolute;left:48px;top:52px;width:210px">
    <div style="position:absolute;right:40px;top:60px;background:#0E0E0E;color:#FFC72C;padding:12px 18px;display:flex;flex-direction:column;align-items:flex-end">
        <span style="font-size:11px;font-weight:700;letter-spacing:3px;color:#fff">COTIZACIÓN N°</span>
        <span style="font-family:'Anton',sans-serif;font-size:40px;line-height:1">{{ $quote->number }}</span>
    </div>

    <div style="position:absolute;left:48px;top:200px;width:370px">
        <div style="font-weight:700;font-size:15px;letter-spacing:6px;color:#FFC72C;margin-bottom:16px">COTIZACIÓN EMPRESARIAL</div>
        <div class="anton" style="font-size:100px;line-height:.92;color:#fff">DOTACIÓN</div>
        <div class="anton" style="font-size:64px;line-height:1;color:#FFC72C;margin-top:4px">EMPRESARIAL</div>
        <div style="margin-top:24px;width:330px;font-size:18px;line-height:1.45;font-weight:600">Chaquetas, busos y prendas personalizadas con la marca de tu empresa</div>
        <div style="margin-top:22px;display:flex;gap:8px;flex-wrap:wrap;width:340px">
            <span style="border:2px solid #FFC72C;color:#FFC72C;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:1px">+10 AÑOS</span>
            <span style="border:2px solid #fff;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:1px">LOGO BORDADO</span>
            <span style="border:2px solid #fff;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:1px">TELAS LAFAYETTE</span>
            <span style="border:2px solid #fff;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:1px">PRECIOS AL POR MAYOR</span>
        </div>
    </div>

    <div style="position:absolute;left:0;right:0;bottom:-1px;height:196px;background:#0E0E0E;border-top:8px solid #FF6B1A;box-sizing:border-box;padding:30px 48px;display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:28px">
        <div style="display:flex;flex-direction:column;gap:6px;min-width:0">
            <span style="font-size:11px;font-weight:700;letter-spacing:3px;color:#FFC72C">PARA</span>
            <span style="font-size:16px;font-weight:700;line-height:1.3">{{ $quote->school }}</span>
            <span style="font-size:13px;line-height:1.5;color:#D6D6D6">{{ $quote->customer_name }}@if($company['role']) · {{ $company['role'] }}@endif
                @if($company['nit'])<br>NIT {{ $company['nit'] }}@endif @if($company['city']) · {{ $company['city'] }}@endif</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px">
            <span style="font-size:11px;font-weight:700;letter-spacing:3px;color:#FFC72C">DE</span>
            <span style="font-size:16px;font-weight:700">G'LAUREN</span>
            <span style="font-size:13px;line-height:1.5;color:#D6D6D6">NIT 1033779133-6<br>Calle 48K 5A-27 sur, Bogotá</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px">
            <span style="font-size:11px;font-weight:700;letter-spacing:3px;color:#FFC72C">CONTACTO</span>
            <span style="font-size:16px;font-weight:700">{{ $phone }}</span>
            <span style="font-size:13px;line-height:1.5;color:#D6D6D6">chaquetasdeprom.com<br>{{ $quote->created_at->locale('es')->translatedFormat('d \d\e F Y') }}</span>
        </div>
    </div>
</div>

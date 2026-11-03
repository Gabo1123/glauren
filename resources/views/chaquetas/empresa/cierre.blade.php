@php
    $steps = [
        ['01', 'Aprobación', 'Revisas la cotización, ajustamos detalles y firmas el contrato en línea.'],
        ['02', 'Anticipo 50%', 'Con el anticipo separamos telas y arrancamos tu pedido.'],
        ['03', 'Diseño y tallas', 'Te enviamos el diseño con tu logo y la tabla de tallas del equipo.'],
        ['04', 'Confección', 'Corte, confección, bordado o estampado y control de calidad.'],
        ['05', 'Entrega', 'Entregamos empacado por persona y se paga el saldo del 50%.'],
    ];
@endphp
<div class="pg dark">
    <div aria-hidden="true" style="position:absolute;inset:0;background:#FFC72C;clip-path:polygon(0 72%,100% 56%,100% 100%,0 100%)"></div>
    <div aria-hidden="true" style="position:absolute;inset:0;background:#FF6B1A;clip-path:polygon(0 68.5%,100% 52.5%,100% 54.5%,0 70.5%)"></div>
    <img src="/img/cotizacion/logo-light.png" alt="G'Lauren" style="position:absolute;left:48px;top:44px;width:150px">
    <div style="position:absolute;right:48px;top:54px;text-align:right">
        <div style="font-size:10px;font-weight:800;letter-spacing:3px">COTIZACIÓN N°</div>
        <div style="font-family:'Anton',sans-serif;font-size:26px;line-height:1;color:#FFC72C">{{ $quote->number }}</div>
    </div>

    <div style="position:absolute;left:48px;right:48px;top:140px;display:flex;flex-direction:column;gap:16px">
        <div class="kick" style="color:#FFC72C">CÓMO TRABAJAMOS</div>
        <h1 class="h1" style="color:#fff;font-size:52px">Del sí a la <span style="color:#FFC72C">entrega</span></h1>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px">
            @foreach($steps as [$num, $t, $d])
                <div style="display:flex;gap:14px;align-items:center;background:#1A1A1A;border:1.5px solid #333;border-radius:16px;padding:12px 16px">
                    <span style="flex:0 0 46px;height:46px;border-radius:50%;background:{{ $loop->last ? '#FF6B1A' : '#FFC72C' }};color:#0E0E0E;display:flex;align-items:center;justify-content:center;font-family:'Anton',sans-serif;font-size:19px">{{ $num }}</span>
                    <div><div class="anton" style="font-size:19px;line-height:1.1">{{ $t }}</div><div style="font-size:12.5px;color:#D9D9D9;line-height:1.4">{{ $d }}</div></div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="position:absolute;left:48px;right:48px;bottom:56px;display:flex;flex-direction:column;gap:14px;color:#0E0E0E">
        <div class="anton" style="font-size:60px;line-height:.95">¿LISTOS PARA VESTIR<br>A TU EQUIPO?</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;padding-top:8px;border-top:3px solid #0E0E0E">
            <div style="display:flex;flex-direction:column;gap:3px"><span style="font-size:11px;font-weight:800;letter-spacing:3px">WHATSAPP / TEL</span><span style="font-size:19px;font-weight:800">{{ $phone }}</span></div>
            <div style="display:flex;flex-direction:column;gap:3px"><span style="font-size:11px;font-weight:800;letter-spacing:3px">WEB</span><span style="font-size:19px;font-weight:800">chaquetasdeprom.com</span></div>
            <div style="display:flex;flex-direction:column;gap:3px"><span style="font-size:11px;font-weight:800;letter-spacing:3px">DIRECCIÓN</span><span style="font-size:14px;font-weight:700;line-height:1.35">Calle 48K 5A-27 sur, Bogotá<br>NIT 1033779133-6</span></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600">
            <span>Cotización no válida como factura de compra.</span>
            <span style="background:#0E0E0E;color:#FFC72C;font-weight:800;padding:4px 10px;border-radius:6px">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>
</div>

@php
    $why = [
        ['bi' => 'M12 2l3 6.5 7 .8-5.2 4.8 1.5 6.9L12 17.5 5.7 21l1.5-6.9L2 9.3l7-.8z', 't' => 'Tu marca bordada', 'd' => 'Logo bordado o estampado con los colores exactos de tu empresa.'],
        ['bi' => 'M4 4h16v16H4zM4 9h16M9 4v16', 't' => 'Telas premium', 'd' => 'Lafayette y Patprimo, cremalleras YES. Hechas para el uso diario.'],
        ['bi' => 'M1 7h13v9H1zM14 10h4l3 3v3h-7', 't' => 'Entrega 25–35 días', 'd' => 'Días hábiles desde la aprobación del diseño y el anticipo.'],
        ['bi' => 'M12 2l8 3v6c0 5-3.5 9-8 11-4.5-2-8-6-8-11V5zM9 12l2 2 4-4', 't' => 'Garantía', 'd' => 'Por defectos de fabricación: confección y cremalleras.'],
        ['bi' => 'M2 6h20v13H2zM2 10h20M6 15h4', 't' => 'Pago 50 / 50', 'd' => '50% para iniciar y 50% contra entrega, por Bancolombia.'],
        ['bi' => 'M6 2h9l5 5v15H6zM14 2v6h6M9 14h8M9 18h5', 't' => 'Contrato formal', 'd' => 'Prestación de servicios con firma en línea y seguimiento del pedido.'],
    ];
@endphp
<div class="pg">
    @include('chaquetas.empresa._bar')
    <div style="position:absolute;left:48px;right:48px;top:122px;bottom:70px;display:flex;flex-direction:column;justify-content:space-between">
        <div style="display:flex;gap:24px;align-items:stretch">
            <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:12px;justify-content:center">
                <div class="kick">QUIÉNES SOMOS</div>
                <h1 class="h1">Vestimos equipos que <span class="hl">llevan tu marca</span></h1>
                <p style="margin:0;font-size:14.5px;line-height:1.6;color:#2A2A2A">En <b>G'Lauren</b> llevamos más de 10 años confeccionando chaquetas y prendas personalizadas. Hoy llevamos esa experiencia a las empresas: prendas cómodas, resistentes y con la identidad de tu marca, para que tu equipo se vea y se sienta como uno solo.</p>
            </div>
            <div style="flex:0 0 270px;position:relative;padding:0 12px 12px 0">
                <div aria-hidden="true" style="position:absolute;left:12px;top:12px;right:0;bottom:0;background:#FFC72C;border-radius:18px"></div>
                <img src="/img/cotizacion/trabajos/trabajo-3.jpg" alt="Prendas G'Lauren" style="position:relative;width:258px;height:290px;object-fit:cover;border-radius:18px;border:3px solid #0E0E0E;display:block;box-sizing:border-box">
                <div style="position:absolute;left:-16px;bottom:-4px;width:92px;height:92px;border-radius:50%;background:#FF6B1A;border:3px solid #0E0E0E;display:flex;flex-direction:column;align-items:center;justify-content:center;transform:rotate(-8deg)">
                    <span style="font-family:'Anton',sans-serif;font-size:30px;line-height:1">+10</span><span style="font-size:10px;font-weight:800;letter-spacing:1px">AÑOS</span>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
            @foreach([['+10', 'años confeccionando prendas'], ['+500', 'colegios ya visten G\'Lauren'], ['50', 'marcas aliadas: Lafayette, Patprimo, YES'], ['1×25', 'chaqueta de obsequio por cada 25']] as [$v, $l])
                <div style="background:#fff;border:2px solid #0E0E0E;border-radius:16px;padding:14px 14px 12px;box-shadow:4px 4px 0 #FFC72C">
                    <div style="font-family:'Anton',sans-serif;font-size:36px;line-height:1">{{ $v }}</div>
                    <div style="font-size:12px;font-weight:700;line-height:1.3;color:#2A2A2A;margin-top:3px">{{ $l }}</div>
                </div>
            @endforeach
        </div>

        <div style="background:#0E0E0E;border-radius:22px;padding:20px">
            <div class="anton" style="font-size:26px;color:#fff;margin-bottom:14px">¿Por qué <span style="color:#FFC72C">G'Lauren</span>?</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
                @foreach($why as $w)
                    <div style="background:#1A1A1A;border:1.5px solid #333;border-radius:14px;padding:12px;display:flex;gap:10px;align-items:flex-start">
                        <span style="flex:0 0 36px;height:36px;border-radius:11px;background:#FFC72C;display:flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0E0E0E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $w['bi'] }}"/></svg></span>
                        <div><div class="anton" style="font-size:15px;color:#fff;line-height:1.1;margin-bottom:3px">{{ $w['t'] }}</div><div style="font-size:11px;line-height:1.4;color:#D9D9D9">{{ $w['d'] }}</div></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:12px;height:150px">
            @foreach(['trabajo-5', 'trabajo-1', 'trabajo-6'] as $t)
                <img src="/img/cotizacion/trabajos/{{ $t }}.jpg" alt="Trabajo G'Lauren" style="width:100%;height:150px;object-fit:cover;border-radius:16px;border:3px solid #0E0E0E;box-sizing:border-box">
            @endforeach
        </div>
    </div>
    @include('chaquetas.empresa._foot')
</div>

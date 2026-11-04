@php $many = count($lines) > 7; @endphp
<div class="pg">
    @include('chaquetas.empresa._bar')
    <div style="position:absolute;left:48px;right:48px;top:116px;bottom:70px;display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;justify-content:space-between;align-items:flex-end">
            <div style="display:flex;flex-direction:column;gap:6px">
                <div class="kick">RESUMEN</div>
                <h1 class="h1">Tu <span class="hl">pedido</span></h1>
            </div>
            <span style="font-size:12px;font-weight:700;color:#444">{{ $quote->school }} · COP</span>
        </div>

        <table class="tb" @if($many) style="font-size:12px" @endif>
            <thead><tr><th style="width:56px"></th><th>PRENDA</th><th class="r">{{ $school ? 'EST.' : 'CANT.' }}</th><th class="r">{{ $school ? 'POR ESTUDIANTE' : 'VALOR UNIDAD' }}</th><th class="r">SUBTOTAL</th></tr></thead>
            <tbody>
                @foreach($lines as $l)
                    <tr>
                        <td>@if($l['photo_url'])<img src="{{ $l['photo_url'] }}" alt="">@else<span style="display:block;width:40px;height:40px;border-radius:8px;background:#F4F3EE"></span>@endif</td>
                        <td><b>{{ $l['name'] }}</b>@if($l['garment'])<br><span style="font-size:11px;color:#666">{{ $l['garment'] }}</span>@endif</td>
                        <td class="r">{{ number_format($l['qty'], 0, ',', '.') }}</td>
                        <td class="r m">{{ $money($l['unit_price']) }}</td>
                        <td class="r m">{{ $money($l['subtotal']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td></td><td>TOTAL</td><td class="r">{{ number_format($totals['units'], 0, ',', '.') }} {{ $school ? 'prendas' : 'und.' }}</td><td></td><td class="r m">{{ $money($totals['total']) }}</td></tr>
            </tfoot>
        </table>

        @if($school)
        <div style="background:#FFC72C;border-radius:22px;padding:18px 20px;display:flex;flex-direction:column;gap:12px">
            <div style="display:flex;justify-content:space-between;align-items:baseline">
                <span class="anton" style="font-size:30px;line-height:1">INCLUIDO EN TU PROM</span>
                <span style="font-size:12px;font-weight:800">Para toda la promoción · sin costo</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat({{ count(config('cotizacion.included')) }},1fr);gap:8px">
                @foreach(config('cotizacion.included') as $inc)
                    <div style="background:#fff;border-radius:14px;padding:10px;text-align:center;font-family:'Anton',sans-serif;font-size:14px;line-height:1.15;text-transform:uppercase;display:flex;align-items:center;justify-content:center">{{ $inc }}</div>
                @endforeach
            </div>
            <div style="background:#0E0E0E;color:#fff;border-radius:16px;padding:12px 14px;display:flex;align-items:center;gap:14px">
                <span class="anton" style="font-size:40px;line-height:1;color:#FFC72C;white-space:nowrap">1×25</span>
                <div>
                    <div class="anton" style="font-size:18px;line-height:1.1">1 chaqueta de obsequio por cada 25 unidades</div>
                    <div style="font-size:12px;font-weight:700;color:#FFC72C;margin-top:3px">
                        @if($totals['gift_jackets'] > 0) Tu promoción recibe {{ $totals['gift_jackets'] }} chaqueta{{ $totals['gift_jackets'] > 1 ? 's' : '' }} de obsequio @else Te faltan {{ $totals['missing_for_gift'] }} unidades para recibirla @endif
                        · Hermanos o primos: 10% de descuento
                    </div>
                </div>
            </div>
        </div>

        @else
        <div style="background:#FFC72C;border-radius:22px;padding:18px 20px;display:flex;flex-direction:column;gap:12px">
            <div style="display:flex;justify-content:space-between;align-items:baseline">
                <span class="anton" style="font-size:30px;line-height:1">OBSEQUIOS INCLUIDOS</span>
                <span style="font-size:12px;font-weight:800">Sin costo para tu empresa</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:10px">
                <div style="background:#fff;border-radius:16px;padding:14px;display:flex;align-items:center;gap:12px">
                    <span style="flex:0 0 52px;height:52px;border-radius:50%;background:#0E0E0E;display:flex;align-items:center;justify-content:center"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFC72C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 13H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></span>
                    <div><div class="anton" style="font-size:22px;line-height:1">Tula</div><div style="font-size:12px;font-weight:700;color:#333">de obsequio con tu pedido</div></div>
                </div>
                <div style="background:#0E0E0E;color:#fff;border-radius:16px;padding:14px;display:flex;align-items:center;gap:14px">
                    <span class="anton" style="font-size:44px;line-height:1;color:#FFC72C;white-space:nowrap">1×25</span>
                    <div>
                        <div class="anton" style="font-size:19px;line-height:1.1">1 chaqueta adicional de obsequio por cada 25 unidades</div>
                        <div style="font-size:12px;font-weight:700;color:#FFC72C;margin-top:4px">
                            @if($totals['gift_jackets'] > 0)
                                Tu pedido recibe {{ $totals['gift_jackets'] }} chaqueta{{ $totals['gift_jackets'] > 1 ? 's' : '' }} de obsequio
                            @else
                                Te faltan {{ $totals['missing_for_gift'] }} unidades para recibirla
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endif

        <div style="background:#0E0E0E;border-radius:22px;padding:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
            <div class="cond"><b>Entrega</b><span>25–35 días hábiles{{ $school ? ', según los colegios atendidos.' : ' desde la aprobación y el anticipo.' }}</span></div>
            <div class="cond"><b>Pago 50 / 50</b><span>50% para iniciar y 50% contra entrega, por Bancolombia.</span></div>
            <div class="cond"><b>Garantía</b><span>{{ $school ? 'Todo el año escolar, por' : 'Por' }} defectos de fabricación: confección y cremalleras.</span></div>
            <div class="cond"><b>Validez</b><span>Esta cotización es válida por {{ $company['validity'] }} días.</span></div>
        </div>

        <div style="margin-top:auto;border:3px solid #0E0E0E;border-radius:20px;padding:18px 22px;display:flex;align-items:center;gap:18px;background:#fff;box-shadow:6px 6px 0 #FF6B1A">
            <span style="flex:0 0 56px;height:56px;border-radius:16px;background:#FFC72C;display:flex;align-items:center;justify-content:center"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#0E0E0E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3.4-7"/><path d="M21 4v5h-5"/></svg></span>
            <div>
                <div class="anton" style="font-size:22px;line-height:1.1">{{ $school ? '¿Ajustamos colores, bordados o prendas?' : '¿Ajustamos cantidades, tallas o prendas?' }}</div>
                <div style="font-size:13px;font-weight:600;color:#2A2A2A;margin-top:3px">{{ $school ? 'Escríbenos y te enviamos la cotización actualizada. Personalizamos colores, nombres, bordados y estampados de tu promoción.' : 'Escríbenos y te enviamos la cotización actualizada. Personalizamos colores, logo, bordados y estampados.' }}</div>
            </div>
        </div>
    </div>
    @include('chaquetas.empresa._foot')
</div>

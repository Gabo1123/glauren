<div class="pg">
    @include('chaquetas.empresa._bar')
    <div style="position:absolute;left:48px;right:48px;top:116px;display:flex;justify-content:space-between;align-items:flex-end">
        <div style="display:flex;flex-direction:column;gap:6px">
            <div class="kick">TU COTIZACIÓN{{ $parts > 1 ? ' · '.$part.' DE '.$parts : '' }}</div>
            <h1 class="h1">Prendas <span class="hl">cotizadas</span></h1>
        </div>
        <span style="background:#0E0E0E;color:#FFC72C;font-size:11px;font-weight:800;letter-spacing:2px;padding:8px 14px;border-radius:999px">VALORES POR UNIDAD · AL POR MAYOR</span>
    </div>

    <div style="position:absolute;left:48px;right:55px;top:214px;display:flex;flex-direction:column;gap:26px">
        @forelse($chunk as $idx => $l)
            <div class="item">
                <div class="ph">
                    @if($l['photo_url'])
                        <img src="{{ $l['photo_url'] }}" alt="{{ $l['name'] }}">
                    @else
                        <div class="none"><svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#BBB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3l5 4-3 3-1-1v12H7V9L6 10 3 7l5-4c1 1.5 2.3 2 4 2s3-.5 4-2z"/></svg>{{ $l['garment'] ?: 'Prenda' }}</div>
                    @endif
                    @if($l['garment'])<span class="tag">{{ $l['garment'] }}</span>@endif
                    <span class="no">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info">
                    <h2 class="nm">{{ $l['name'] }}</h2>
                    <ul class="feat">
                        @forelse(array_slice($l['features'], 0, 9) as $f)
                            <li><span class="ck"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0E0E0E" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span>{{ $f }}</li>
                        @empty
                            <li style="color:#777">Personalizada con el logo y los colores de tu empresa.</li>
                        @endforelse
                    </ul>
                    <div class="nums">
                        <div><small>CANTIDAD</small><b>{{ number_format($l['qty'], 0, ',', '.') }}</b><em>unidades</em></div>
                        <div><small>VALOR UNIDAD</small><b>{{ $money($l['unit_price']) }}</b><em>al por mayor</em></div>
                        <div class="yy"><small>SUBTOTAL</small><b>{{ $money($l['subtotal']) }}</b></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="item" style="align-items:center;justify-content:center;color:#777">Aún no hay prendas en esta cotización.</div>
        @endforelse
    </div>
    @include('chaquetas.empresa._foot')
</div>

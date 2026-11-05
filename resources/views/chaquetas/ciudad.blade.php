@php
    use App\Modules\Chaquetas\Models\Setting;
    $brand = \App\Modules\Chaquetas\Support\Brand::name();
    $n = $city['name'];
    $wa = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573021106592'));
    $waMsg = rawurlencode("¡Hola! Quiero cotizar chaquetas de prom para un colegio en {$n}.");
    $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
    $year = now()->month >= 8 ? now()->year + 1 : now()->year;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'ciudad-'.$city['slug'],
    'seoTitleOverride' => "Chaquetas de prom en {$n} {$year} · desde ".$money($minPrice)." · {$brand}",
    'seoDescriptionOverride' => "Chaquetas de prom y busos de promoción para colegios de {$n}: beisbolera desde ".$money($products['beisbolera']['price']).", estructurada ".$money($products['estructurada']['price']).", combos desde ".$money($minCombo).". Entrega en 25 a 35 días hábiles, pago 50/50 y contrato en línea.",
    'ogImageOverride' => url('/img/cotizacion/chaqueta.jpg'),
    'canonical' => route('ciudades.show', $city['slug']),
    'breadcrumbs' => [['name' => 'Inicio', 'url' => url('/')], ['name' => 'Chaquetas de prom por ciudad', 'url' => route('ciudades.index')], ['name' => $n, 'url' => route('ciudades.show', $city['slug'])]],
    'faqs' => $faqs,
])
@include('chaquetas.partials.city-style')
</head>
<body>
<nav class="cn">
    <a href="{{ url('/') }}" class="brand"><span class="mk">G</span><span class="wm">{{ $brand }}</span></a>
    <a href="https://wa.me/{{ $wa }}?text={{ $waMsg }}" class="btn btn-wa" target="_blank" rel="noopener">WhatsApp</a>
</nav>

<header class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="ey">{{ $city['region'] }} · Promoción {{ $year }}</span>
            <h1>Chaquetas de prom en <span class="gold">{{ $n }}</span></h1>
            <p class="lead">{{ $city['intro'] }}</p>
            <p class="lead" style="font-size:16px">Beisbolera desde <b class="gold">{{ $money($products['beisbolera']['price']) }}</b> · Estructurada <b class="gold">{{ $money($products['estructurada']['price']) }}</b> · Combos desde <b class="gold">{{ $money($minCombo) }}</b> por estudiante.</p>
            <div class="cta">
                <a href="{{ url('/') }}#cotiza" class="btn btn-gold">Cotizar en 1 minuto</a>
                <a href="https://wa.me/{{ $wa }}?text={{ $waMsg }}" class="btn btn-wa" target="_blank" rel="noopener">Escribir por WhatsApp</a>
            </div>
        </div>
        <div class="pic"><img src="{{ url('/img/cotizacion/chaqueta.jpg') }}" alt="Chaqueta de prom estructurada bordada para una promoción de {{ $n }}" loading="eager"></div>
    </div>
</header>

<section>
    <div class="wrap">
        <span class="ey">Precios {{ now()->year }}</span>
        <h2>Cuánto cuesta una chaqueta de prom en {{ $n }}</h2>
        <table>
            <thead><tr><th>Prenda</th><th>Bordados</th><th>Tela</th><th style="text-align:right">Por estudiante</th></tr></thead>
            <tbody>
            @foreach($products as $p)
                <tr><td><b>{{ $p['name'] }}</b></td><td>{{ $p['embroidery'] }}</td><td>{{ $p['fabric'] }}</td><td class="p">{{ $money($p['price']) }}</td></tr>
            @endforeach
            </tbody>
        </table>
        <p style="color:var(--muted);margin-top:12px;font-size:14px">Combos de chaqueta + buso (con polo o camiseta) desde {{ $money($minCombo) }}, con 3% de descuento ya aplicado. 1 chaqueta de obsequio por cada 25 unidades y 10% para hermanos o primos.</p>
    </div>
</section>

<section>
    <div class="wrap">
        <span class="ey">Así trabajamos en {{ $n }}</span>
        <h2>De la cotización a la entrega</h2>
        <ol class="steps">
            <li><b>Cotización</b><span>Pídela en 1 minuto en la web o por WhatsApp, con precios y diseños.</span></li>
            <li><b>{{ $city['local'] ? 'Presentación en el colegio' : 'Reunión virtual' }}</b><span>{{ $city['local'] ? 'Vamos a tu colegio con muestras de telas y bordados.' : 'Te mostramos muestras y ajustamos el diseño por videollamada.' }}</span></li>
            <li><b>Contrato en línea</b><span>Se firma desde el celular. Anticipo del 50% para iniciar.</span></li>
            <li><b>{{ $city['local'] ? 'Medidas en el colegio' : 'Tallas estándar' }}</b><span>{{ $city['local'] ? 'Tomamos las medidas de cada estudiante (o tallas estándar si lo prefieren).' : 'Cada estudiante elige su talla (S, M, L, XL…) con nuestra tabla y guía.' }}</span></li>
            <li><b>Producción</b><span>25 a 35 días hábiles, con fotos reales del avance en el portal.</span></li>
            <li><b>Seguimiento</b><span>Cada estudiante ve su chaqueta, su talla y su pago en su portal.</span></li>
            <li><b>Entrega</b><span>{{ $city['local'] ? 'En el colegio, con globos y fotografía.' : 'Te enviamos el pedido, cada prenda empacada con su nombre.' }}</span></li>
            <li><b>Saldo</b><span>Se paga el 50% restante al entregar.</span></li>
        </ol>
        @unless($city['local'])
            <p class="note" style="margin-top:16px">La toma de medidas en el colegio, la entrega con globos y el paquete de fotografía de grado son solo para Bogotá y Cundinamarca. En {{ $n }} todo el proceso es virtual y enviamos el pedido.</p>
        @endunless
    </div>
</section>

<section>
    <div class="wrap">
        <span class="ey">Por qué {{ $brand }}</span>
        <h2>Lo que incluye tu promoción</h2>
        <div class="grid3">
            <div class="card"><h3>Taller propio</h3><p>Más de 10 años fabricando chaquetas de prom en Bogotá, con telas Lafayette y cremalleras YES.</p></div>
            <div class="card"><h3>Contrato formal</h3><p>Contrato de prestación de servicios firmado en línea, pagos 50/50 y garantía durante el año escolar.</p></div>
            <div class="card"><h3>Portal con fotos</h3><p>El colegio y cada estudiante siguen la producción con fotos reales y pagan su parte en línea.</p></div>
        </div>
    </div>
</section>

<section>
    <div class="wrap">
        <span class="ey">Preguntas frecuentes</span>
        <h2>Chaquetas de promoción en {{ $n }}</h2>
        @foreach($faqs as $f)
            <details {{ $loop->first ? 'open' : '' }}><summary>{{ $f->question }}</summary><p>{{ $f->answer }}</p></details>
        @endforeach
        <div class="cta" style="margin-top:22px">
            <a href="{{ url('/') }}#cotiza" class="btn btn-gold">Cotizar ahora</a>
            <a href="{{ route('personalizar') }}" class="btn btn-wa" style="background:var(--panel);color:var(--paper)">Diseñar en 3D</a>
        </div>
    </div>
</section>

<section style="border-bottom:0">
    <div class="wrap">
        <span class="ey">Otras ciudades</span>
        <h2 style="font-size:30px">También hacemos chaquetas de prom en</h2>
        <div class="cities">
            @foreach($others as $o)<a href="{{ route('ciudades.show', $o['slug']) }}">{{ $o['name'] }}</a>@endforeach
        </div>
    </div>
</section>

<footer>
    <div class="wrap">© {{ now()->year }} {{ $brand }} · Chaquetas de prom en Colombia · <a href="{{ url('/') }}" class="gold">chaquetasdeprom.com</a></div>
</footer>
</body>
</html>

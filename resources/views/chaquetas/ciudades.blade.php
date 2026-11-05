@php
    $brand = \App\Modules\Chaquetas\Support\Brand::name();
    $local = collect($cities)->filter(fn ($c) => $c['local']);
    $rest = collect($cities)->reject(fn ($c) => $c['local']);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('partials.seo', [
    'seoKey' => 'ciudades',
    'seoTitleOverride' => 'Chaquetas de prom por ciudad en Colombia · '.$brand,
    'seoDescriptionOverride' => 'Chaquetas de prom y busos de promoción para colegios en Bogotá, Cundinamarca, Medellín, Cali, Barranquilla, Bucaramanga y toda Colombia. Precios, tiempos de entrega y cómo trabajamos en cada ciudad.',
    'canonical' => route('ciudades.index'),
    'breadcrumbs' => [['name' => 'Inicio', 'url' => url('/')], ['name' => 'Chaquetas de prom por ciudad', 'url' => route('ciudades.index')]],
])
@include('chaquetas.partials.city-style')
</head>
<body>
<nav class="cn">
    <a href="{{ url('/') }}" class="brand"><span class="mk">G</span><span class="wm">{{ $brand }}</span></a>
    <a href="{{ url('/') }}#cotiza" class="btn btn-gold">Cotizar</a>
</nav>
<header class="hero">
    <div class="wrap">
        <span class="ey">Toda Colombia</span>
        <h1>Chaquetas de prom <span class="gold">por ciudad</span></h1>
        <p class="lead">Fabricamos en Bogotá y atendemos colegios en todo el país. En Bogotá y Cundinamarca vamos al colegio; en las demás ciudades todo el proceso es virtual y enviamos el pedido.</p>
    </div>
</header>
<section>
    <div class="wrap">
        <h2 style="font-size:32px">Bogotá y Cundinamarca <small class="gold" style="font-size:16px">· vamos al colegio</small></h2>
        <div class="cities">@foreach($local as $slug => $c)<a href="{{ route('ciudades.show', $slug) }}">Chaquetas de prom en {{ $c['name'] }}</a>@endforeach</div>
    </div>
</section>
<section style="border-bottom:0">
    <div class="wrap">
        <h2 style="font-size:32px">Resto de Colombia <small class="gold" style="font-size:16px">· proceso virtual y envío</small></h2>
        <div class="cities">@foreach($rest as $slug => $c)<a href="{{ route('ciudades.show', $slug) }}">Chaquetas de prom en {{ $c['name'] }}</a>@endforeach</div>
    </div>
</section>
<footer><div class="wrap">© {{ now()->year }} {{ $brand }} · <a href="{{ url('/') }}" class="gold">chaquetasdeprom.com</a></div></footer>
</body>
</html>

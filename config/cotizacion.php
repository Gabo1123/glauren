<?php

/*
|--------------------------------------------------------------------------
| Catálogo de la cotización G'Lauren
|--------------------------------------------------------------------------
| Precios por estudiante (COP). Los combos se calculan solos:
| suma de los precios unitarios menos el descuento por combinar prendas,
| redondeado a la centena. Cambia aquí los valores y se actualiza todo.
*/

return [

    'combo_discount' => 0.03,

    'products' => [
        'estructurada' => ['name' => 'Chaqueta estructurada o tipo Fórmula 1', 'short' => 'Chaqueta estructurada', 'price' => 202900, 'embroidery' => 5, 'fabric' => 'Lafayette'],
        'beisbolera'   => ['name' => 'Chaqueta beisbolera o chaqueta un faz', 'short' => 'Beisbolera o un faz', 'price' => 169900, 'embroidery' => 5, 'fabric' => 'Lafayette'],
        'doble_faz'    => ['name' => 'Chaqueta doble faz', 'short' => 'Chaqueta doble faz', 'price' => 279900, 'embroidery' => 8, 'fabric' => 'Lafayette'],
        'buso'         => ['name' => 'Buso canguro, con cuello o con cremallera', 'short' => 'Buso', 'price' => 149900, 'embroidery' => 5, 'fabric' => 'Lafayette o lacoste'],
        'deportiva'    => ['name' => 'Camiseta deportiva', 'short' => 'Deportiva', 'price' => 99400, 'embroidery' => 2, 'fabric' => 'Lafayette'],
        'polo'         => ['name' => 'Camiseta tipo polo', 'short' => 'Polo', 'price' => 90000, 'embroidery' => 2, 'fabric' => 'Patprimo o Lafayette (lacoste)'],
    ],

    'combos' => [
        1 => ['items' => ['beisbolera', 'buso']],
        2 => ['items' => ['estructurada', 'buso']],
        3 => ['items' => ['beisbolera', 'buso', 'polo']],
        4 => ['items' => ['estructurada', 'buso', 'polo']],
        5 => ['items' => ['beisbolera', 'buso', 'deportiva']],
        6 => ['items' => ['estructurada', 'buso', 'deportiva']],
        7 => ['items' => ['beisbolera', 'buso', 'polo', 'deportiva'], 'gift' => 'Gorra de regalo'],
        8 => ['items' => ['estructurada', 'buso', 'polo', 'deportiva'], 'gift' => 'Gorra de regalo'],
        9 => ['items' => ['doble_faz', 'buso']],
    ],

    'extras' => [
        'pano'        => ['name' => 'Beisbolera en paño con 5 bordados (en lugar de la estándar)', 'price' => 239900, 'highlight' => true],
        'forro'       => ['name' => 'Forro estampado con nombres de estudiantes', 'price' => 20000],
        'sublimacion' => ['name' => 'Sublimación en todo el buso', 'price' => 15000],
        'bordado_xl'  => ['name' => 'Bordado adicional 24 × 18 cm', 'price' => 12000],
        'capota_nom'  => ['name' => 'Nombres de estudiantes en capota del buso', 'price' => 12000],
        'capota'      => ['name' => 'Capota removible', 'price' => 10000],
        'bordado'     => ['name' => 'Bordado adicional 10 × 13 cm', 'price' => 8000],
        'bolsillo'    => ['name' => 'Bolsillo con cremallera', 'price' => 5000],
        'bolsillo_inv'=> ['name' => 'Bolsillo con cremallera invisible', 'price' => 5000],
    ],

    'included' => ['Bandera por salón de 11', 'Globos en la entrega para fotos', 'Fotografía en la entrega de las chaquetas', 'Tula o termo'],

    'benefits' => [
        ['big' => '10%', 'title' => 'Hermanos o primos', 'text' => 'Descuento del 10% cuando hermanos o primos hacen su pedido con nosotros.'],
        ['big' => '3%', 'title' => 'Al combinar prendas', 'text' => 'Buso + chaqueta o cualquier combo. El descuento ya está aplicado.'],
        ['big' => '1×25', 'title' => 'Chaqueta gratis', 'text' => 'Por cada 25 unidades entregamos una chaqueta de obsequio.'],
    ],

    'conditions' => [
        ['title' => 'Entrega en 25–35 días', 'text' => 'Días hábiles, según los colegios atendidos en ese momento.'],
        ['title' => 'Pago 50 / 50', 'text' => '50% al iniciar y 50% al finalizar, por transferencia a Bancolombia.'],
        ['title' => 'Garantía todo el año', 'text' => 'Defectos de fabricación (confección y cremalleras) durante el año escolar.'],
        ['title' => 'Toma de medidas', 'text' => 'En el colegio (Bogotá y Cundinamarca). Otras ciudades: tallas estándar con tabla y guía.'],
        ['title' => 'Seguimiento en línea', 'text' => 'Portal para el colegio y para cada estudiante, con fotos reales del avance.'],
        ['title' => 'Contrato formal', 'text' => 'Prestación de servicios, con firma en línea.'],
    ],

    'photo_package' => [
        'before' => 189900,
        'price'  => 119000,
        'items'  => [
            'Sesión fotográfica de grado',
            'Carpeta de cuero',
            'Diploma impreso',
            'Estola estampada con escudo y nombre',
            'Fotos digitales en nuestra plataforma',
            '2 fotos impresas con estola y ribete',
            'Anuario de la promoción',
        ],
        'benefit' => 'Estola con ribete y toga GRATIS al contratar el paquete.',
    ],

];

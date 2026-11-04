<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

/**
 * Cotizaciones personalizadas para colegios: las mismas prendas con foto, características y valor
 * por estudiante, dentro de la cotización completa de prom (beneficios, plataforma, proceso, fotografía…).
 * Se guardan como Quote con source = 'personalizada'.
 */
class CustomQuoteController extends BusinessQuoteController
{
    protected string $kind = 'personalizada';

    protected string $routes = 'admin.custom';

    protected function labels(): array
    {
        return [
            'title'       => 'Cotizaciones personalizadas para colegios',
            'subtitle'    => 'Para promociones con un diseño propio: cada prenda con su foto, características y valor por estudiante.',
            'new'         => 'Nueva cotización personalizada',
            'new_title'   => 'Nueva cotización personalizada para colegio',
            'entity'      => 'Colegio',
            'entity_ph'   => 'Ej.: Colegio San José',
            'role_ph'     => 'Representante del curso, rector…',
            'qty'         => 'Estudiantes',
            'units'       => 'Estudiantes',
            'price'       => 'Valor x estudiante',
            'price_ph'    => '189.900',
            'empty'       => 'Aún no hay cotizaciones personalizadas para colegios.',
            'icon'        => 'bi-mortarboard',
            'garment'     => 'Chaqueta',
            'default_qty' => 30,
            'gifts'       => array_merge(config('cotizacion.included', []), ['1 chaqueta de obsequio por cada 25 unidades']),
            'school'      => true,
        ];
    }
}

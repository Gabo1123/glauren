<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Models\Quote;

/**
 * Las 15 hojas de la cotización (las mismas del PDF de diseño), guardadas como HTML
 * estático en resources/cotizacion/paginas. Aquí solo se rellenan los datos del cliente.
 *
 * Para actualizar el diseño: exportar el lienzo y correr resources/cotizacion/convertir.py.
 */
class QuotePages
{
    public static function dir(): string
    {
        return resource_path('cotizacion/paginas');
    }

    public static function css(): string
    {
        return (string) @file_get_contents(self::dir().'/pages.css');
    }

    public static function promYear(): int
    {
        return now()->month >= 8 ? now()->year + 1 : now()->year;
    }

    /** @return array<int, string> HTML de cada hoja, ya con los datos de esta cotización. */
    public static function pages(Quote $quote): array
    {
        $year = self::promYear();
        $detail = $quote->school
            ? $quote->school.' · Promoción '.$year
            : 'Promoción grado 11 · '.$year;
        if ($quote->isCustom() && $quote->company()['course']) {
            $detail = trim(($quote->school ? $quote->school.' · ' : '').$quote->company()['course']);
        }
        if ($quote->quantity > 1) {
            $detail .= ' · '.$quote->quantity.' estudiantes';
        }

        $tokens = [
            '%%NUM%%'          => e((string) ($quote->number ?: $quote->id)),
            '%%PARA_NOMBRE%%'  => e($quote->customer_name ?: 'Estimados padres de familia'),
            '%%PARA_DETALLE%%' => e($detail),
            '%%YEAR%%'         => (string) $year,
        ];

        $files = glob(self::dir().'/[0-9][0-9].html') ?: [];
        sort($files);

        return array_map(fn ($f) => strtr((string) file_get_contents($f), $tokens), $files);
    }
}

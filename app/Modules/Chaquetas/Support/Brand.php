<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Models\Setting;

/** Nombre de marca único en toda la web (los buscadores y la IA confían en nombres consistentes). */
class Brand
{
    public static function name(): string
    {
        $n = trim((string) Setting::get('brand_name', ''));

        return $n === '' || strcasecmp(str_replace(['’', "'", ' '], '', $n), 'glauren') === 0 ? "G'Lauren" : $n;
    }

    /** Logo cuadrado para Google y la IA (mín. 112×112). */
    public static function logoUrl(): string
    {
        return url('/android-chrome-512x512.png');
    }

    /** Imagen por defecto para compartir; ignora la antigua de WordPress que ya no existe. */
    public static function defaultImage(): string
    {
        $og = (string) Setting::get('seo_og_image', '');

        return $og !== '' && ! str_contains($og, '/wp-content/') ? $og : url('/img/cotizacion/chaqueta.jpg');
    }
}

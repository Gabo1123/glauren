<?php

namespace App\Modules\Chaquetas\Support;

/**
 * Lee config/cotizacion.php y calcula combos y totales.
 * Valor combo = suma de unitarios − descuento, redondeado a la centena.
 */
class QuoteCatalog
{
    public static function products(): array
    {
        return config('cotizacion.products', []);
    }

    public static function extras(): array
    {
        return config('cotizacion.extras', []);
    }

    public static function combos(): array
    {
        $products = self::products();
        $discount = (float) config('cotizacion.combo_discount', 0.03);
        $out = [];
        foreach (config('cotizacion.combos', []) as $n => $combo) {
            $items = array_values(array_filter($combo['items'], fn ($k) => isset($products[$k])));
            $full = array_sum(array_map(fn ($k) => $products[$k]['price'], $items));
            $out[$n] = [
                'number' => $n,
                'items'  => $items,
                'names'  => array_map(fn ($k) => $products[$k]['short'], $items),
                'full'   => $full,
                'price'  => (int) (round($full * (1 - $discount) / 100) * 100),
                'gift'   => $combo['gift'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * Normaliza la selección guardada en quotes.items y calcula el valor por estudiante.
     * Formato: ['combo' => 2|null, 'products' => ['buso', ...], 'extras' => ['forro', ...]]
     *
     * @return array{lines: array<int, array{label:string, price:int}>, unit_total:int}
     */
    public static function summarize(?array $items): array
    {
        $items = $items ?? [];
        $lines = [];
        $combos = self::combos();
        $products = self::products();
        $extras = self::extras();

        if (! empty($items['combo']) && isset($combos[$items['combo']])) {
            $c = $combos[$items['combo']];
            $lines[] = ['label' => 'Combo '.$c['number'].': '.implode(' + ', $c['names']), 'price' => $c['price']];
        }
        foreach ((array) ($items['products'] ?? []) as $k) {
            if (isset($products[$k])) {
                $lines[] = ['label' => $products[$k]['name'], 'price' => (int) $products[$k]['price']];
            }
        }
        foreach ((array) ($items['extras'] ?? []) as $k) {
            if (isset($extras[$k])) {
                $lines[] = ['label' => $extras[$k]['name'], 'price' => (int) $extras[$k]['price']];
            }
        }

        return ['lines' => $lines, 'unit_total' => array_sum(array_column($lines, 'price'))];
    }

    public static function money(int|float $v): string
    {
        return '$'.number_format((float) $v, 0, ',', '.');
    }
}

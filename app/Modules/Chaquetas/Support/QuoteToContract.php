<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Models\Quote;

/** Pasa lo cotizado a los campos del contrato (cotización ganada → contrato en un paso). */
class QuoteToContract
{
    /** Producto del cotizador → prenda del combo del alumno. */
    private const GARMENT = [
        'estructurada' => 'chaqueta', 'beisbolera' => 'chaqueta', 'doble_faz' => 'chaqueta',
        'buso' => 'buzo', 'polo' => 'polo', 'deportiva' => 'polo',
    ];

    public static function fields(Quote $quote): array
    {
        $qty = max(0, (int) $quote->quantity);
        $summary = $quote->summary();
        $lines = $summary['lines'] ?? [];
        $school = $quote->schoolRecord?->name ?: ($quote->school ?: 'la promoción');

        if ($quote->isCustom()) {
            $biz = $quote->businessLines();
            $total = (int) $quote->businessTotals()['total'];
            $qty = $qty ?: (int) max(array_column($biz, 'qty') ?: [0]);
            $unit = $qty > 0 ? (int) round($total / $qty) : (int) array_sum(array_column($biz, 'unit_price'));
            $pieces = array_map(fn ($l) => $l['qty'].' '.mb_strtolower($l['name'] ?: $l['garment']), $biz);
            $details = implode("\n\n", array_map(fn ($l) => mb_strtoupper($l['name'] ?: $l['garment'])
                .($l['features'] ? ' — '.implode('. ', $l['features']).'.' : '')
                .' Valor unitario: $'.number_format($l['unit_price'], 0, ',', '.').'.', $biz));
            $garments = array_map(fn ($l) => self::garmentFromText($l['garment'].' '.$l['name']), $biz);
        } else {
            $unit = (int) ($summary['unit_total'] ?? 0);
            $total = $unit * $qty;
            $names = array_map(fn ($l) => $l['label'], $lines);
            $pieces = $qty ? array_map(fn ($n) => $qty.' × '.$n, $names) : $names;
            $details = implode("\n", array_map(fn ($l) => '• '.$l['label'].': $'.number_format($l['price'], 0, ',', '.').' por estudiante', $lines));
            $items = (array) $quote->items;
            $keys = (array) ($items['products'] ?? []);
            if (! empty($items['combo'])) {
                $keys = array_merge($keys, (array) (QuoteCatalog::combos()[$items['combo']]['items'] ?? []));
            }
            $garments = array_map(fn ($k) => self::GARMENT[$k] ?? null, $keys);
        }

        $gift = $quote->giftJackets();
        $objeto = 'Diseño y confección de '.($pieces ? implode(', ', $pieces) : 'las prendas de grado')
            .' para '.$school.'.'
            .($gift > 0 ? ' Incluye '.$gift.' chaqueta(s) de obsequio.' : '');

        return array_filter([
            'jacket_type_id'  => $quote->jacket_type_id,
            'default_combo'   => self::combo(array_filter($garments)),
            'price_per_unit'  => $unit ?: null,
            'combo_value'     => $unit ?: null,
            'contract_total'  => $total ?: null,
            'total_words'     => $total ? NumberToWords::pesos($total) : null,
            'deposit_amount'  => $total && ($pct = (int) \App\Modules\Chaquetas\Models\Setting::get('deposit_percent', '30')) > 0
                ? (int) (round($total * $pct / 100 / 1000) * 1000) : null,
            'objeto'          => $objeto,
            'product_details' => $details ?: null,
            'notes'           => 'Desde cotización '.$quote->code().($qty ? ' · '.$qty.' estudiantes' : '').'.',
        ], fn ($v) => $v !== null && $v !== '');
    }

    private static function garmentFromText(string $t): ?string
    {
        $t = mb_strtolower($t);

        return match (true) {
            str_contains($t, 'chaqueta') || str_contains($t, 'chaleco') => 'chaqueta',
            str_contains($t, 'buso') || str_contains($t, 'buzo') => 'buzo',
            str_contains($t, 'polo') || str_contains($t, 'camiseta') => 'polo',
            str_contains($t, 'gorra') => 'gorra',
            default => null,
        };
    }

    /** El combo de alumno que mejor coincide con las prendas cotizadas. */
    private static function combo(array $garments): ?string
    {
        $g = array_values(array_unique($garments));
        if (! $g) {
            return null;
        }
        $best = null;
        foreach (\App\Modules\Chaquetas\Models\Student::COMBOS as $key => $c) {
            $hits = count(array_intersect($c['garments'], $g));
            $extra = count(array_diff($c['garments'], $g));
            $score = $hits * 10 - $extra * 11;
            if ($best === null || $score > $best[1]) {
                $best = [$key, $score];
            }
        }

        return $best[0];
    }
}

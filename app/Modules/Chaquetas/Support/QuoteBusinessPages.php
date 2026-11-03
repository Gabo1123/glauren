<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;

/**
 * Hojas A4 (794 × 1123 px) de la cotización para empresas, con el mismo estilo del PDF de colegios.
 * Portada · Quiénes somos · Prendas (2 por hoja) · Resumen y obsequios · Telas · Proceso y contacto.
 */
class QuoteBusinessPages
{
    /** @return array<int, array{class:string, html:string}> */
    public static function pages(Quote $quote): array
    {
        $lines = $quote->businessLines();
        $chunks = array_chunk($lines, 2, true) ?: [[]];
        $total = 4 + count($chunks) + 1;
        $data = [
            'quote'   => $quote,
            'lines'   => $lines,
            'totals'  => $quote->businessTotals(),
            'company' => $quote->company(),
            'phone'   => Setting::get('contact_phone', '302 110 6592'),
            'money'   => fn ($v) => '$'.number_format((float) $v, 0, ',', '.'),
            'pages'   => $total,
        ];

        $out = [];
        $n = 1;
        $add = function (string $view, array $extra = []) use (&$out, &$n, $data) {
            $out[] = ['class' => 'be', 'html' => view('chaquetas.empresa.'.$view, $data + $extra + ['n' => $n])->render()];
            $n++;
        };

        $add('portada');
        $add('nosotros');
        foreach ($chunks as $k => $chunk) {
            $add('prendas', ['chunk' => $chunk, 'part' => $k + 1, 'parts' => count($chunks)]);
        }
        $add('resumen');

        // Telas y tecnología: la misma hoja de la cotización de colegios.
        $telas = QuotePages::dir().'/06.html';
        if (is_file($telas)) {
            $html = strtr((string) file_get_contents($telas), ['%%NUM%%' => e((string) $quote->number)]);
            $html = preg_replace('#(border-radius: 6px">)06(</span>)#', '${1}'.str_pad((string) $n, 2, '0', STR_PAD_LEFT).'${2}', $html);
            $out[] = ['class' => 'pg-06', 'html' => $html];
            $n++;
        }
        $add('cierre');

        return $out;
    }
}

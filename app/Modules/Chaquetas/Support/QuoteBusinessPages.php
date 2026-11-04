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
            'school'  => $quote->isSchoolCustom(),
        ];

        if ($quote->isSchoolCustom()) {
            return self::schoolPages($quote, $data, $chunks);
        }

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

    /**
     * Colegio personalizada: la cotización completa de prom (mismas hojas del PDF de colegios),
     * cambiando catálogo, precios y combos por las prendas cotizadas a la medida.
     */
    private static function schoolPages(Quote $quote, array $data, array $chunks): array
    {
        $static = QuotePages::pages($quote);           // 15 hojas del lienzo, ya con los datos del cliente
        $out = [];
        $n = 1;
        $canvas = function (int $index) use (&$out, &$n, $static) {
            if (isset($static[$index])) {
                $out[] = ['class' => 'pg-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), 'html' => self::renumber($static[$index], $n)];
                $n++;
            }
        };
        $view = function (string $name, array $extra = []) use (&$out, &$n, $data) {
            $out[] = ['class' => 'be', 'html' => view('chaquetas.empresa.'.$name, $data + $extra + ['n' => $n])->render()];
            $n++;
        };

        foreach ([0, 1, 2, 3, 4, 5] as $i) {           // portada, quiénes somos, beneficios, plataforma, trabajos, telas
            $canvas($i);
        }
        foreach ($chunks as $k => $chunk) {
            $view('prendas', ['chunk' => $chunk, 'part' => $k + 1, 'parts' => count($chunks)]);
        }
        $view('resumen');
        foreach ([12, 13, 14] as $i) {                 // proceso, paquete de fotografía, adicionales y contacto
            $canvas($i);
        }

        return $out;
    }

    /** Cambia el número de página del pie de una hoja del lienzo. */
    private static function renumber(string $html, int $n): string
    {
        return (string) preg_replace('#(border-radius: 6px">)\d{2}(</span>)#', '${1}'.str_pad((string) $n, 2, '0', STR_PAD_LEFT).'${2}', $html, 1);
    }
}

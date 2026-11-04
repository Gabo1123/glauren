<?php

namespace App\Modules\Chaquetas\Support;

/** Convierte valores en pesos a letras (español de Colombia), para el "valor en letras" del contrato. */
class NumberToWords
{
    private const U = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez',
        'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve', 'veinte',
        'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'];
    private const D = ['', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    private const C = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    /** 5589000 → "Cinco millones quinientos ochenta y nueve mil pesos m/cte." */
    public static function pesos(int|float $value): string
    {
        $n = (int) round($value);
        $w = self::apocope(self::words($n));
        // "un millón de pesos" / "dos millones de pesos": lleva "de" cuando termina en millón(es) exactos
        $de = $n > 0 && $n % 1000000 === 0 ? ' de' : '';

        return ucfirst($w).$de.' pesos m/cte.';
    }

    public static function words(int $n): string
    {
        if ($n === 0) {
            return 'cero';
        }
        $parts = [];
        $millions = intdiv($n, 1000000);
        $rest = $n % 1000000;
        if ($millions > 0) {
            $parts[] = $millions === 1 ? 'un millón' : self::apocope(self::upTo999999($millions)).' millones';
        }
        if ($rest > 0) {
            $parts[] = self::upTo999999($rest);
        }

        return implode(' ', $parts);
    }

    private static function upTo999999(int $n): string
    {
        $th = intdiv($n, 1000);
        $r = $n % 1000;
        $out = [];
        if ($th === 1) {
            $out[] = 'mil';
        } elseif ($th > 1) {
            $out[] = self::apocope(self::upTo999($th)).' mil';
        }
        if ($r > 0) {
            $out[] = self::upTo999($r);
        }

        return implode(' ', $out);
    }

    private static function upTo999(int $n): string
    {
        if ($n === 100) {
            return 'cien';
        }
        $c = intdiv($n, 100);
        $r = $n % 100;
        $out = $c ? [self::C[$c]] : [];
        if ($r > 0) {
            $out[] = $r < 30 ? self::U[$r] : self::D[intdiv($r, 10)].($r % 10 ? ' y '.self::U[$r % 10] : '');
        }

        return implode(' ', $out);
    }

    /** "veintiuno mil" → "veintiún mil", "treinta y uno mil" → "treinta y un mil". */
    private static function apocope(string $w): string
    {
        return preg_replace(['/veintiuno$/', '/uno$/'], ['veintiún', 'un'], $w);
    }
}

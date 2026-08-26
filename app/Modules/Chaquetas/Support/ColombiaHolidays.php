<?php

namespace App\Modules\Chaquetas\Support;

use Carbon\Carbon;

/**
 * Festivos de Colombia y cálculo de días hábiles.
 * Incluye festivos fijos, los movidos al lunes (Ley Emiliani) y los de Semana Santa.
 */
class ColombiaHolidays
{
    /** @var array<int,array<string,bool>> caché de festivos por año */
    private static array $cache = [];

    /** Devuelve los festivos (Y-m-d => true) de un año. */
    public static function forYear(int $year): array
    {
        if (isset(self::$cache[$year])) {
            return self::$cache[$year];
        }

        $days = [];
        $add = function (Carbon $d) use (&$days) {
            $days[$d->format('Y-m-d')] = true;
        };

        // Fijos (no se mueven)
        foreach ([[1, 1], [5, 1], [7, 20], [8, 7], [12, 8], [12, 25]] as [$m, $d]) {
            $add(Carbon::create($year, $m, $d));
        }

        // Movibles al lunes siguiente (Ley Emiliani)
        foreach ([[1, 6], [3, 19], [6, 29], [8, 15], [10, 12], [11, 1], [11, 11]] as [$m, $d]) {
            $add(self::nextMonday(Carbon::create($year, $m, $d)));
        }

        // Domingo de Pascua
        $easter = self::easter($year);
        // Jueves y Viernes Santo (no se mueven)
        $add($easter->copy()->subDays(3));
        $add($easter->copy()->subDays(2));
        // Ascensión (+43), Corpus Christi (+64), Sagrado Corazón (+71) → ya caen en lunes
        $add($easter->copy()->addDays(43));
        $add($easter->copy()->addDays(64));
        $add($easter->copy()->addDays(71));

        return self::$cache[$year] = $days;
    }

    public static function isHoliday(Carbon $date): bool
    {
        return isset(self::forYear((int) $date->year)[$date->format('Y-m-d')]);
    }

    /** ¿Es día hábil? (lunes a viernes y no festivo) */
    public static function isBusinessDay(Carbon $date): bool
    {
        return ! $date->isWeekend() && ! self::isHoliday($date);
    }

    /** Suma N días hábiles colombianos a una fecha. */
    public static function addBusinessDays(Carbon $start, int $days): Carbon
    {
        $d = $start->copy();
        $count = 0;
        while ($count < $days) {
            $d->addDay();
            if (self::isBusinessDay($d)) {
                $count++;
            }
        }

        return $d;
    }

    /** Domingo de Pascua (algoritmo de Gauss/computus gregoriano). */
    private static function easter(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }

    private static function nextMonday(Carbon $date): Carbon
    {
        return $date->isMonday() ? $date->copy() : $date->copy()->next(Carbon::MONDAY);
    }
}

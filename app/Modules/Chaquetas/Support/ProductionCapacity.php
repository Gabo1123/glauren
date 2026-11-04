<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Capacidad del taller: cuántas prendas caben por mes y cuántas ya están comprometidas
 * según la fecha de entrega de cada contrato. Cuenta prendas cosidas (chaqueta, buzo, polo); las gorras no.
 */
class ProductionCapacity
{
    public const SEWN = ['chaqueta', 'buzo', 'polo'];

    public static function capacity(): int
    {
        return max(0, (int) Setting::get('monthly_capacity', '0'));
    }

    public static function configured(): bool
    {
        return self::capacity() > 0;
    }

    /** Prendas que ocupa un contrato en el taller. */
    public static function contractUnits(Contract $c): int
    {
        $students = $c->relationLoaded('students') ? $c->students : $c->students()->get();
        if ($students->isNotEmpty()) {
            return (int) $students->sum(fn (Student $s) => array_sum(array_intersect_key($s->garmentUnits() ?: ['chaqueta' => 1], array_flip(self::SEWN))));
        }
        // Aún sin alumnos: lo estimamos con el valor del contrato
        $unit = (float) ($c->combo_value ?: $c->price_per_unit);
        $people = $unit > 0 && $c->contract_total ? (int) round((float) $c->contract_total / $unit) : 0;
        $perStudent = count(array_intersect(Student::COMBOS[$c->defaultCombo()]['garments'] ?? ['chaqueta'], self::SEWN)) ?: 1;

        return $people * $perStudent;
    }

    /** Prendas que ocuparía una cotización si se gana. */
    public static function quoteUnits(Quote $q): int
    {
        if ($q->isCustom()) {
            return (int) collect($q->businessLines())
                ->filter(fn ($l) => ! str_contains(mb_strtolower($l['garment'].' '.$l['name']), 'gorra'))
                ->sum('qty');
        }
        $items = (array) $q->items;
        $keys = (array) ($items['products'] ?? []);
        if (! empty($items['combo'])) {
            $keys = array_merge($keys, (array) (QuoteCatalog::combos()[$items['combo']]['items'] ?? []));
        }

        return max(1, count($keys)) * max(1, (int) $q->quantity);
    }

    /** Fecha estimada de entrega si el cliente firmara hoy. */
    public static function estimatedDelivery(?Carbon $from = null): Carbon
    {
        $days = (int) Setting::get('production_days', '35') ?: 35;

        return ColombiaHolidays::addBusinessDays(($from ?? now())->copy(), $days);
    }

    /** Contratos que ocupan taller (aprobados y en producción; "cotizado" cuenta como tentativo). */
    private static function activeContracts(): Collection
    {
        return Contract::with(['school', 'students'])
            ->whereNotIn('production_status', ['entregado'])
            ->get()
            ->filter(fn ($c) => $c->deliveryDate() !== null);
    }

    /**
     * Carga por mes desde el mes actual.
     *
     * @return array<string, array{key:string,label:string,month:Carbon,confirmed:int,tentative:int,total:int,pct:int,free:int,contracts:array}>
     */
    public static function plan(int $months = 6): array
    {
        $cap = self::capacity();
        $start = now()->startOfMonth();
        $out = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $out[$m->format('Y-m')] = ['key' => $m->format('Y-m'), 'label' => ucfirst($m->locale('es')->isoFormat('MMMM YYYY')), 'month' => $m,
                'confirmed' => 0, 'tentative' => 0, 'total' => 0, 'pct' => 0, 'free' => $cap, 'contracts' => []];
        }
        foreach (self::activeContracts() as $c) {
            $d = $c->deliveryDate();
            $k = $d->lt($start) ? $start->format('Y-m') : $d->format('Y-m');   // atrasados cuentan en el mes actual
            if (! isset($out[$k])) {
                continue;
            }
            $u = self::contractUnits($c);
            $tent = $c->production_status === 'cotizado';
            $out[$k][$tent ? 'tentative' : 'confirmed'] += $u;
            $out[$k]['contracts'][] = ['contract' => $c, 'units' => $u, 'tentative' => $tent, 'date' => $d];
        }
        foreach ($out as &$row) {
            $row['total'] = $row['confirmed'] + $row['tentative'];
            $row['pct'] = $cap > 0 ? (int) round($row['total'] / $cap * 100) : 0;
            $row['free'] = max(0, $cap - $row['total']);
            usort($row['contracts'], fn ($a, $b) => $a['date'] <=> $b['date']);
        }

        return $out;
    }

    /**
     * ¿Caben $units prendas con entrega en la fecha dada?
     *
     * @return array{configured:bool,month:string,load:int,after:int,capacity:int,pct:int,over:bool,suggest:?string}
     */
    public static function check(Carbon $date, int $units, ?int $exceptContract = null): array
    {
        $cap = self::capacity();
        $plan = self::plan(12);
        $key = $date->lt(now()->startOfMonth()) ? now()->format('Y-m') : $date->format('Y-m');
        $load = isset($plan[$key]) ? $plan[$key]['total'] : 0;
        if ($exceptContract && isset($plan[$key])) {
            foreach ($plan[$key]['contracts'] as $row) {
                if ($row['contract']->id === $exceptContract) {
                    $load -= $row['units'];
                }
            }
        }
        $after = $load + $units;
        $suggest = null;
        if ($cap > 0 && $after > $cap) {
            foreach ($plan as $k => $row) {
                if ($k > $key && $row['total'] + $units <= $cap) {
                    $suggest = $row['label'];
                    break;
                }
            }
        }

        return [
            'configured' => $cap > 0,
            'month'      => ucfirst($date->copy()->locale('es')->isoFormat('MMMM YYYY')),
            'load'       => $load,
            'after'      => $after,
            'capacity'   => $cap,
            'units'      => $units,
            'pct'        => $cap > 0 ? (int) round($after / $cap * 100) : 0,
            'over'       => $cap > 0 && $after > $cap,
            'suggest'    => $suggest,
        ];
    }
}

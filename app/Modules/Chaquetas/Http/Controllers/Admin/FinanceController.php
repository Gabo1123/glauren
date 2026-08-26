<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Expense;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Student;

class FinanceController extends Controller
{
    public function index()
    {
        // Globales
        $expected = (float) Student::sum('amount');
        $received = (float) Student::where('payment_status', 'pagado')->sum('amount');
        $gastos   = (float) Expense::sum('amount');
        $utilidad = $expected - $gastos;
        $margen   = $expected > 0 ? round($utilidad / $expected * 100) : 0;

        // Utilidad por colegio
        $expBySchool = Expense::selectRaw('school_id, SUM(amount) t')->groupBy('school_id')->pluck('t', 'school_id');
        $porColegio = School::with('contracts.students')->get()->map(function ($s) use ($expBySchool) {
            $students = $s->contracts->flatMap->students;
            $exp = (float) $students->sum('amount');
            $rec = (float) $students->where('payment_status', 'pagado')->sum('amount');
            $gas = (float) ($expBySchool[$s->id] ?? 0);

            return [
                'name'     => $s->name,
                'expected' => $exp,
                'received' => $rec,
                'gastos'   => $gas,
                'utilidad' => $exp - $gas,
                'alumnos'  => $students->count(),
            ];
        })->filter(fn ($r) => $r['expected'] > 0 || $r['gastos'] > 0)
          ->sortByDesc('utilidad')->values();

        // Gastos por mes (últimos 6)
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $months[$d->format('Y-m')] = ['label' => ucfirst($d->translatedFormat('M')), 'gastos' => 0.0];
        }
        Expense::selectRaw("DATE_FORMAT(date, '%Y-%m') ym, SUM(amount) t")
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')->get()
            ->each(function ($r) use (&$months) {
                if (isset($months[$r->ym])) {
                    $months[$r->ym]['gastos'] = (float) $r->t;
                }
            });

        // Gastos por categoría
        $porCategoria = Expense::selectRaw('category, SUM(amount) t')->groupBy('category')
            ->pluck('t', 'category');

        return view('admin.finance.index', compact(
            'expected', 'received', 'gastos', 'utilidad', 'margen',
            'porColegio', 'months', 'porCategoria'
        ) + ['pending' => $expected - $received, 'categories' => Expense::CATEGORIES]);
    }
}

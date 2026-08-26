<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Expense;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $stages = Contract::STAGES;
        $pipeline = Contract::selectRaw('production_status, count(*) as c')
            ->groupBy('production_status')->pluck('c', 'production_status');

        $stats = [
            'contracts_total'    => Contract::count(),
            'contracts_active'   => Contract::where('production_status', '!=', 'entregado')->count(),
            'schools'            => School::count(),
            'students'           => Student::count(),
            'quotes'             => Quote::count(),
            'quotes_new'         => Quote::where('status', 'nuevo')->count(),
            'ingresos_recibidos' => (float) Student::where('payment_status', 'pagado')->sum('amount'),
            'ingresos_esperados' => (float) Student::sum('amount'),
            'gastos'             => (float) Expense::sum('amount'),
            'gastos_mes'         => (float) Expense::whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount'),
        ];
        $stats['por_cobrar'] = max(0, $stats['ingresos_esperados'] - $stats['ingresos_recibidos']);
        $stats['utilidad']   = $stats['ingresos_esperados'] - $stats['gastos'];
        $stats['margen']     = $stats['ingresos_esperados'] > 0
            ? round($stats['utilidad'] / $stats['ingresos_esperados'] * 100) : 0;

        $recientes = Quote::with('jacketType')->latest()->take(5)->get();
        $entregas = Contract::with('school')->withCount('students')
            ->whereIn('production_status', ['toma_medidas', 'en_produccion', 'control_calidad', 'listo'])
            ->orderByDesc('year')->take(6)->get();
        $gastosRecientes = Expense::with('school')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'pipeline', 'stages', 'recientes', 'entregas', 'gastosRecientes'));
    }
}

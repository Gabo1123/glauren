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

        // Embudo de ventas (cotizaciones)
        $mes = Quote::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
        $ganadas = Quote::where('status', 'cerrado')->count();
        $decididas = $ganadas + Quote::where('status', 'cancelado')->count();
        $ventas = [
            'mes'          => (clone $mes)->count(),
            'enviadas_mes' => (clone $mes)->whereNotNull('sent_at')->count(),
            'vistas_mes'   => (clone $mes)->whereNotNull('viewed_at')->count(),
            'ganadas'      => $ganadas,
            'tasa_cierre'  => $decididas > 0 ? round($ganadas / $decididas * 100) : 0,
            'abiertas'     => Quote::whereIn('status', Quote::OPEN_STATUSES)->count(),
            'por_llamar'   => Quote::whereIn('status', Quote::OPEN_STATUSES)->whereNotNull('follow_up_at')->whereDate('follow_up_at', '<=', today())->count(),
            'valor_abierto'=> (float) Quote::whereIn('status', Quote::OPEN_STATUSES)->sum('estimated_price'),
        ];

        $recientes = Quote::with('jacketType')->latest()->take(5)->get();
        $entregas = Contract::with('school')->withCount('students')
            ->whereIn('production_status', ['toma_medidas', 'en_produccion', 'control_calidad', 'listo'])
            ->orderByDesc('year')->take(6)->get();
        $gastosRecientes = Expense::with('school')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'pipeline', 'stages', 'recientes', 'entregas', 'gastosRecientes', 'ventas'));
    }
}

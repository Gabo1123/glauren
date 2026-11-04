<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Support\ProductionCapacity;
use Illuminate\Http\Request;

/** Capacidad del taller por mes. */
class CapacityController extends Controller
{
    public function index(Request $request)
    {
        $months = max(3, min(12, (int) $request->input('meses', 6)));

        return view('admin.capacity.index', [
            'plan'     => ProductionCapacity::plan($months),
            'capacity' => ProductionCapacity::capacity(),
            'months'   => $months,
            'prodDays' => (int) Setting::get('production_days', '35') ?: 35,
            'deposit'  => (int) Setting::get('deposit_percent', '30'),
            'next'     => ProductionCapacity::estimatedDelivery(),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'monthly_capacity' => 'required|integer|min:0|max:100000',
            'production_days'  => 'required|integer|min:5|max:200',
            'deposit_percent'  => 'required|integer|min:0|max:100',
        ]);
        foreach ($data as $k => $v) {
            Setting::put($k, (string) $v);
        }

        return back()->with('ok', 'Capacidad guardada.');
    }
}

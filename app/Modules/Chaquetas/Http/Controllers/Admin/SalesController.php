<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Support\SalesAutomation;
use App\Modules\Chaquetas\Support\StudentSheet;
use Illuminate\Http\Request;

/** Tablero de ventas, cartera y automatizaciones. */
class SalesController extends Controller
{
    public function dashboard(Request $request)
    {
        $months = max(3, min(24, (int) $request->input('meses', 6)));
        $from = now()->startOfMonth()->subMonths($months - 1);
        $quotes = Quote::with('lead')->where('created_at', '>=', $from)->get();

        // Por mes
        $byMonth = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $from->copy()->addMonths($i);
            $byMonth[$m->format('Y-m')] = ['label' => ucfirst($m->locale('es')->isoFormat('MMM YY')), 'created' => 0, 'sent' => 0, 'won' => 0, 'lost' => 0, 'value' => 0, 'won_value' => 0];
        }
        foreach ($quotes as $q) {
            $k = $q->created_at->format('Y-m');
            if (! isset($byMonth[$k])) {
                continue;
            }
            $v = $q->totalValue();
            $byMonth[$k]['created']++;
            $byMonth[$k]['value'] += $v;
            if ($q->sent_at || in_array($q->status, ['enviada', 'vista', 'negociando', 'cerrado'], true)) {
                $byMonth[$k]['sent']++;
            }
            if ($q->status === 'cerrado') {
                $byMonth[$k]['won']++;
                $byMonth[$k]['won_value'] += $v;
            }
            if ($q->status === 'cancelado') {
                $byMonth[$k]['lost']++;
            }
        }

        // Por fuente
        $bySource = $quotes->groupBy(fn ($q) => $q->channel())->map(function ($g, $name) {
            $won = $g->where('status', 'cerrado');

            return [
                'name'      => $name,
                'count'     => $g->count(),
                'won'       => $won->count(),
                'rate'      => $g->count() ? (int) round($won->count() / $g->count() * 100) : 0,
                'won_value' => $won->sum(fn ($q) => $q->totalValue()),
            ];
        })->sortByDesc('won_value')->sortByDesc('won')->values();

        // Embudo actual (todas las abiertas)
        $pipeline = collect(Quote::STATUSES)->map(fn ($label, $key) => [
            'key' => $key, 'label' => $label,
            'count' => Quote::where('status', $key)->count(),
        ])->values();
        $openValue = Quote::whereIn('status', Quote::OPEN_STATUSES)->get()->sum(fn ($q) => $q->totalValue());

        $t = [
            'created'   => $quotes->count(),
            'sent'      => array_sum(array_column($byMonth, 'sent')),
            'won'       => array_sum(array_column($byMonth, 'won')),
            'lost'      => array_sum(array_column($byMonth, 'lost')),
            'won_value' => array_sum(array_column($byMonth, 'won_value')),
            'open'      => $openValue,
        ];
        $t['rate'] = $t['sent'] ? (int) round($t['won'] / $t['sent'] * 100) : 0;
        $t['avg'] = $t['won'] ? (int) round($t['won_value'] / $t['won']) : 0;

        $sales = app(SalesAutomation::class);
        $today = [
            'due'    => $sales->dueToday(),
            'viewed' => $sales->viewedNoReply(),
            'auto'   => $sales->followupCandidates(),
        ];
        $auto = [
            'digest'   => SalesAutomation::digestEnabled(),
            'followup' => SalesAutomation::followupEnabled(),
            'days'     => SalesAutomation::followupDays(),
            'message'  => Setting::get('auto_followup_message') ?: SalesAutomation::DEFAULT_FOLLOWUP,
            'google'   => Setting::get('google_review_url', ''),
            'email'    => Setting::get('contact_notify_email') ?: config('mail.from.address'),
            'phone'    => Setting::get('admin_2fa_phone'),
        ];

        return view('admin.sales.dashboard', compact('byMonth', 'bySource', 'pipeline', 't', 'months', 'today', 'auto'));
    }

    public function saveAutomation(Request $request)
    {
        $data = $request->validate([
            'auto_followup_days'    => 'required|integer|min:1|max:15',
            'auto_followup_message' => 'nullable|string|max:700',
            'google_review_url'     => 'nullable|url|max:300',
        ]);
        Setting::put('sales_digest', $request->boolean('sales_digest') ? '1' : '0');
        Setting::put('auto_followup', $request->boolean('auto_followup') ? '1' : '0');
        Setting::put('auto_followup_days', (string) $data['auto_followup_days']);
        Setting::put('auto_followup_message', trim((string) ($data['auto_followup_message'] ?? '')) ?: null);
        Setting::put('google_review_url', $data['google_review_url'] ?? null);

        return back()->with('ok', 'Automatizaciones guardadas.')->withFragment('auto');
    }

    /** Envía el resumen de hoy ya mismo (para probar). */
    public function sendDigestNow(SalesAutomation $sales)
    {
        $n = $sales->sendDigest();

        return back()->with('ok', $n ? "Resumen enviado a tu correo/WhatsApp: $n cliente(s)." : 'Hoy no hay clientes para contactar; no se envió nada.');
    }

    /** Cartera: recaudado vs. pendiente por colegio. */
    public function cartera(Request $request)
    {
        $rows = $this->carteraRows($request->boolean('todos'));
        $tot = [
            'total'   => $rows->sum('total'),
            'paid'    => $rows->sum('paid'),
            'review'  => $rows->sum('review'),
            'balance' => $rows->sum('balance'),
            'students' => $rows->sum('students'),
            'pending_students' => $rows->sum('pending_students'),
        ];
        $tot['pct'] = $tot['total'] > 0 ? (int) round($tot['paid'] / $tot['total'] * 100) : 0;

        return view('admin.sales.cartera', ['rows' => $rows, 'tot' => $tot, 'all' => $request->boolean('todos')]);
    }

    public function carteraExport(Request $request)
    {
        $out = [['Contrato', 'Colegio', 'Año', 'Etapa', 'Alumnos', 'Valor total', 'Recaudado', 'En revisión', 'Saldo', '% recaudo', 'Alumnos que deben']];
        foreach ($this->carteraRows($request->boolean('todos')) as $r) {
            $out[] = [$r['code'], $r['school'], $r['year'], $r['stage'], $r['students'], $r['total'], $r['paid'], $r['review'], $r['balance'], $r['pct'].'%', $r['pending_students']];
        }

        return StudentSheet::download('cartera-'.now()->format('Y-m-d').'.csv', $out);
    }

    public function carteraRows(bool $all)
    {
        $q = Contract::with(['school', 'students', 'priceChanges']);
        if (! $all) {
            $q->where('production_status', '!=', 'cotizado');
        }

        return $q->get()->map(function (Contract $c) {
            $unit = (float) ($c->price_per_unit ?? 0);
            $total = $c->totalValue();
            if ($total <= 0 && $c->contract_total) {
                $total = (float) $c->contract_total;
            }
            $deposit = (float) $c->deposit_paid;
            $paid = $c->paidValue() + $deposit;
            $review = (float) $c->students->where('payment_status', 'en_revision')->sum(fn ($s) => (float) ($s->amount ?? $unit));

            return [
                'contract'         => $c,
                'code'             => $c->code ?: '#'.$c->id,
                'school'           => $c->school?->name ?? '—',
                'year'             => $c->year,
                'stage'            => $c->stageLabel(),
                'students'         => $c->students->count(),
                'pending_students' => $c->students->filter(fn ($s) => ! $s->isPaid())->count(),
                'total'            => $total,
                'paid'             => $paid,
                'review'           => $review + ($c->depositStatus() === 'en_revision' ? $c->depositDue() : 0),
                'deposit'          => $deposit,
                'deposit_status'   => $c->depositStatus(),
                'balance'          => max(0, $total - $paid),
                'pct'              => $total > 0 ? (int) round($paid / $total * 100) : 0,
            ];
        })->filter(fn ($r) => $r['total'] > 0 || $r['students'] > 0)->sortByDesc('balance')->values();
    }
}

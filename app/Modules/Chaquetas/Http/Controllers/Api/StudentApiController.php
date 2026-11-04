<?php

namespace App\Modules\Chaquetas\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Support\DeliverySurvey;
use Illuminate\Http\Request;

/** API del estudiante: su chaqueta, medidas y pago. */
class StudentApiController extends Controller
{
    private function me(Request $request): Student
    {
        return $request->attributes->get('api_owner');
    }

    private function methods(): array
    {
        return array_values(array_filter([
            Setting::get('pay_allow_nequi', '1') === '1' ? ['key' => 'nequi', 'label' => 'Nequi', 'account' => Setting::get('pay_nequi', '')] : null,
            Setting::get('pay_allow_bancolombia', '1') === '1' ? ['key' => 'bancolombia', 'label' => 'Bancolombia', 'account' => Setting::get('pay_bancolombia', '')] : null,
            Setting::get('pay_allow_efectivo', '0') === '1' ? ['key' => 'efectivo', 'label' => 'Efectivo', 'account' => null] : null,
        ]));
    }

    public function show(Request $request)
    {
        $s = $this->me($request)->load('contract.school', 'contract.photos');
        $c = $s->contract;

        return response()->json([
            'name' => $s->name, 'nickname' => $s->nickname, 'document' => $s->document,
            'school' => $c?->school?->name, 'year' => $c?->year,
            'combo' => $s->comboLabel(), 'size' => $s->size, 'measured' => $s->hasMeasurements(),
            'stage' => $c?->production_status, 'stage_label' => $c?->stageLabel(), 'stage_index' => $c?->stageIndex(), 'stages' => Contract::STAGES,
            'delivery_date' => $c?->deliveryDate()?->toDateString(),
            'payment' => [
                'status' => $s->payment_status ?: 'pendiente',
                'amount' => (float) ($s->amount ?? $c?->price_per_unit ?? 0),
                'school_pays' => $c?->school?->payment_mode === 'colegio',
                'methods' => $this->methods(),
            ],
            'photos' => $c ? $c->photos->sortByDesc('id')->take(20)->map(fn ($p) => ['url' => $p->url(), 'caption' => $p->caption])->values() : [],
            'survey_url' => $c && $c->production_status === 'entregado' ? DeliverySurvey::url($c) : null,
            'web_url' => route('student.home'),
        ]);
    }

    /** Reporta el pago con su comprobante; queda "en revisión" hasta que G'Lauren lo apruebe. */
    public function pay(Request $request)
    {
        $s = $this->me($request);
        if ($s->isPaid()) {
            return response()->json(['message' => 'Tu pago ya está aprobado.'], 422);
        }
        $allowed = array_column($this->methods(), 'key');
        $data = $request->validate([
            'method' => 'required|in:'.implode(',', $allowed ?: ['ninguno']),
            'proof'  => ($request->input('method') === 'efectivo' ? 'nullable' : 'required').'|image|mimes:jpg,jpeg,png,webp,heic|max:8192',
        ], ['proof.required' => 'Sube la foto del comprobante de tu transferencia.']);
        $update = ['payment_status' => 'en_revision', 'payment_method' => $data['method'], 'payment_declared_at' => now()];
        if ($request->hasFile('proof')) {
            $update['payment_proof'] = $request->file('proof')->store('comprobantes', 'public');
        }
        $s->update($update);

        return $this->show($request);
    }
}

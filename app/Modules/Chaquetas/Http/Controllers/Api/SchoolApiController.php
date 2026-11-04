<?php

namespace App\Modules\Chaquetas\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Http\Controllers\Portal\PortalController;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** API del colegio: su contrato, alumnos, pagos y anticipo. */
class SchoolApiController extends Controller
{
    private function own(Contract $contract): Contract
    {
        abort_unless($contract->school_id === Auth::user()->school?->id, 403, 'Este contrato no es de tu colegio.');

        return $contract;
    }

    public function contracts()
    {
        $school = Auth::user()->school;

        return response()->json(['school' => $school->name, 'data' => $school->contracts()->with('students')->orderByDesc('year')->get()->map(fn ($c) => [
            'id' => $c->id, 'code' => $c->code, 'year' => $c->year, 'stage' => $c->production_status, 'stage_label' => $c->stageLabel(),
            'students' => $c->students->count(), 'paid' => $c->students->where('payment_status', 'pagado')->count(),
            'delivery_date' => $c->deliveryDate()?->toDateString(),
        ])->values()]);
    }

    public function contract(Contract $contract)
    {
        $c = $this->own($contract)->load(['students' => fn ($q) => $q->orderBy('name'), 'photos']);
        $s = $c->students;

        return response()->json([
            'id' => $c->id, 'code' => $c->code, 'year' => $c->year,
            'stage' => $c->production_status, 'stage_label' => $c->stageLabel(), 'stage_index' => $c->stageIndex(), 'stages' => Contract::STAGES,
            'delivery_date' => $c->deliveryDate()?->toDateString(),
            'signed' => $c->isSigned(),
            'sign_url' => ! $c->isSigned() && $c->sign_token ? route('contrato.firmar', $c->sign_token) : null,
            'payments' => [
                'total' => $s->count(), 'paid' => $s->where('payment_status', 'pagado')->count(),
                'review' => $s->where('payment_status', 'en_revision')->count(),
                'pending' => $s->filter(fn ($x) => ! in_array($x->payment_status, ['pagado', 'en_revision'], true))->count(),
            ],
            'deposit' => AdminApiController::depositRow($c) + ['account' => Setting::get('contratista_cuenta', '')],
            'students' => $s->map(fn ($x) => [
                'id' => $x->id, 'name' => $x->name, 'document' => $x->document, 'nickname' => $x->nickname, 'phone' => $x->phone,
                'measured' => $x->hasMeasurements(), 'payment_status' => $x->payment_status ?: 'pendiente',
            ])->values(),
            'photos' => $c->photos->sortByDesc('id')->take(30)->map(fn ($p) => ['url' => $p->url(), 'phase' => $p->phase, 'caption' => $p->caption])->values(),
        ]);
    }

    public function addStudent(Request $request, Contract $contract)
    {
        $this->own($contract);
        $request->merge(['gender' => ['hombre' => 'M', 'mujer' => 'F'][$request->input('gender')] ?? $request->input('gender')]);
        app(PortalController::class)->addStudent($request, $contract);

        return $this->contract($contract->fresh());
    }

    public function deposit(Request $request, Contract $contract)
    {
        $this->own($contract);
        app(PortalController::class)->depositProof($request, $contract);

        return $this->contract($contract->fresh());
    }
}

<?php

namespace App\Modules\Chaquetas\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Http\Controllers\Admin\QuoteController;
use App\Modules\Chaquetas\Http\Controllers\Admin\SalesController;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Support\ImageOptimizer;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use App\Modules\Chaquetas\Support\SalesAutomation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** API del equipo G'Lauren (rol admin). */
class AdminApiController extends Controller
{
    /* ============================ Inicio ============================ */

    public function today(SalesAutomation $sales)
    {
        $due = $sales->dueToday();
        $viewed = $sales->viewedNoReply()->whereNotIn('id', $due->pluck('id'));
        $monthWon = Quote::where('status', 'cerrado')->where('updated_at', '>=', now()->startOfMonth())->get();

        return response()->json([
            'call_today'      => $due->map(fn ($q) => $this->quoteRow($q))->values(),
            'viewed_no_reply' => $viewed->map(fn ($q) => $this->quoteRow($q))->values(),
            'counts' => [
                'call_today'       => $due->count(),
                'viewed_no_reply'  => $viewed->count(),
                'new_quotes'       => Quote::where('status', 'nuevo')->count(),
                'payments_review'  => Student::where('payment_status', 'en_revision')->count(),
                'deposits_review'  => Contract::whereNotNull('deposit_declared_at')->get()->filter(fn ($c) => $c->depositStatus() === 'en_revision')->count(),
                'in_production'    => Contract::whereIn('production_status', ['toma_medidas', 'en_produccion', 'control_calidad', 'listo'])->count(),
                'won_this_month'   => $monthWon->count(),
                'won_value_month'  => $monthWon->sum(fn ($q) => $q->totalValue()),
            ],
        ]);
    }

    public function catalog()
    {
        return response()->json([
            'combos'   => collect(QuoteCatalog::combos())->map(fn ($c) => ['number' => $c['number'], 'names' => $c['names'], 'price' => $c['price'], 'gift' => $c['gift']])->values(),
            'products' => collect(QuoteCatalog::products())->map(fn ($p, $k) => ['key' => $k, 'name' => $p['name'], 'price' => (int) $p['price']])->values(),
            'extras'   => collect(QuoteCatalog::extras())->map(fn ($p, $k) => ['key' => $k, 'name' => $p['name'], 'price' => (int) $p['price']])->values(),
            'stages'   => Contract::STAGES,
            'quote_statuses' => Quote::STATUSES,
            'client_fields'  => Quote::CLIENT_FIELDS,
            'combos_student' => collect(Student::COMBOS)->map(fn ($c, $k) => ['key' => $k, 'label' => $c['label']])->values(),
        ]);
    }

    /* ========================= Cotizaciones ========================= */

    public function quotes(Request $request)
    {
        $q = Quote::query()->latest();
        if ($s = trim((string) $request->input('q'))) {
            $q->where(fn ($w) => $w->where('customer_name', 'like', "%$s%")->orWhere('school', 'like', "%$s%")
                ->orWhere('customer_phone', 'like', "%$s%")->orWhere('number', 'like', '%'.preg_replace('/\D/', '', $s).'%'));
        }
        if ($st = $request->input('status')) {
            $q->where('status', $st);
        }
        $page = $q->paginate(30);

        return response()->json(['data' => collect($page->items())->map(fn ($x) => $this->quoteRow($x)), 'next' => $page->hasMorePages() ? $page->currentPage() + 1 : null]);
    }

    public function quote(Quote $quote)
    {
        return response()->json($this->quoteDetail($quote));
    }

    /** Nuevo cliente desde el celular: crea el contacto y su cotización. */
    public function storeQuote(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:120',
            'customer_phone' => 'required|string|max:40',
            'customer_email' => 'nullable|email|max:160',
            'school'         => 'nullable|string|max:160',
            'quantity'       => 'nullable|integer|min:1|max:100000',
            'combo'          => ['nullable', 'integer', Rule::in(array_keys(QuoteCatalog::combos()))],
            'products'       => 'nullable|array',
            'products.*'     => ['string', Rule::in(array_keys(QuoteCatalog::products()))],
            'extras'         => 'nullable|array',
            'extras.*'       => ['string', Rule::in(array_keys(QuoteCatalog::extras()))],
            'notes'          => 'nullable|string|max:2000',
        ]);
        $lead = Lead::create(['name' => $data['customer_name'], 'phone' => $data['customer_phone'], 'email' => $data['customer_email'] ?? null,
            'school' => $data['school'] ?? null, 'source' => 'app', 'accepted_terms' => true]);
        $items = ['combo' => $data['combo'] ?? null, 'products' => array_values($data['products'] ?? []), 'extras' => array_values($data['extras'] ?? [])];
        $qty = (int) ($data['quantity'] ?? 1);
        $quote = Quote::create([
            'lead_id' => $lead->id, 'customer_name' => $data['customer_name'], 'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null, 'school' => $data['school'] ?? null,
            'quantity' => $qty, 'items' => $items, 'estimated_price' => QuoteCatalog::summarize($items)['unit_total'] * $qty,
            'status' => 'nuevo', 'source' => 'manual', 'follow_up_at' => today()->addDays(2), 'notes' => $data['notes'] ?? null,
        ]);
        $quote->log('nota', 'Creada desde la app.');

        return response()->json($this->quoteDetail($quote), 201);
    }

    /** Ficha del cliente (mismos campos que en la web). */
    public function updateClient(Request $request, Quote $quote)
    {
        app(QuoteController::class)->updateClient($request, $quote);

        return response()->json($this->quoteDetail($quote->fresh()));
    }

    /** Cambia la etapa: nuevo/contactado/enviada/vista/negociando, perdida, reabrir o ganada (crea el colegio). */
    public function quoteStage(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'stage'       => ['required', Rule::in(['nuevo', 'contactado', 'enviada', 'vista', 'negociando', 'perdida', 'reabrir', 'ganada'])],
            'school_name' => 'nullable|string|max:160',
            'city'        => 'nullable|string|max:120',
        ]);
        $stage = $data['stage'];
        if ($stage === 'ganada') {
            if ($quote->isBusiness()) {
                $quote->update(['status' => 'cerrado', 'follow_up_at' => null]);
            } elseif (! $quote->school_id) {
                $request->merge(['school_name' => $data['school_name'] ?: ($quote->school ?: $quote->customer_name)]);
                app(QuoteController::class)->convertToSchool($request, $quote);
            }
        } elseif ($stage === 'perdida') {
            $quote->update(['status' => 'cancelado', 'follow_up_at' => null]);
        } elseif ($stage === 'reabrir') {
            $quote->update(['status' => 'negociando', 'follow_up_at' => today()]);
        } else {
            $update = ['status' => $stage];
            if ($stage === 'enviada') {
                $update['sent_at'] = $quote->sent_at ?? now();
                $update['follow_up_at'] = today()->addDays(2);
            }
            $quote->update($update);
        }
        $quote->log('estado', 'Etapa desde la app: '.(Quote::JOURNEY[$stage][0] ?? ucfirst($stage)).'.');

        return response()->json($this->quoteDetail($quote->fresh()));
    }

    /** Próxima llamada y/o nota rápida. */
    public function followUp(Request $request, Quote $quote)
    {
        $data = $request->validate(['follow_up_at' => 'nullable|date', 'note' => 'nullable|string|max:500', 'clear' => 'nullable|boolean']);
        if ($request->boolean('clear')) {
            $quote->update(['follow_up_at' => null]);
        } elseif (! empty($data['follow_up_at'])) {
            $quote->update(['follow_up_at' => $data['follow_up_at']]);
        }
        if (! empty($data['note'])) {
            $quote->log('nota', $data['note']);
        }

        return response()->json($this->quoteDetail($quote->fresh()));
    }

    /** La app abrió WhatsApp con la cotización: queda como enviada. */
    public function markSent(Quote $quote)
    {
        $quote->markSent();
        $quote->log('whatsapp', 'Cotización enviada por WhatsApp desde la app.');

        return response()->json($this->quoteDetail($quote->fresh()));
    }

    /* ========================== Contratos =========================== */

    public function contracts(Request $request)
    {
        $q = Contract::with(['school', 'students'])->latest();
        if ($request->boolean('activos', true)) {
            $q->where('production_status', '!=', 'entregado');
        }

        return response()->json(['data' => $q->get()->map(fn ($c) => $this->contractRow($c))->values()]);
    }

    public function contract(Contract $contract)
    {
        $contract->load(['school', 'students' => fn ($q) => $q->orderBy('name'), 'photos']);

        return response()->json($this->contractRow($contract) + [
            'students' => $contract->students->map(fn ($s) => $this->studentRow($s))->values(),
            'photos'   => $contract->photos->sortByDesc('id')->take(30)->map(fn ($p) => ['id' => $p->id, 'url' => $p->url(), 'phase' => $p->phase, 'caption' => $p->caption])->values(),
            'deposit'  => $this->depositRow($contract),
            'stages'   => Contract::STAGES,
        ]);
    }

    public function contractStage(Request $request, Contract $contract)
    {
        $data = $request->validate(['production_status' => ['required', Rule::in(array_keys(Contract::STAGES))]]);
        $contract->update($data);   // dispara avisos por WhatsApp y la encuesta al entregar

        return $this->contract($contract->fresh());
    }

    public function photos(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'photos'   => 'required|array|max:20',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp,heic|max:12288',
            'caption'  => 'nullable|string|max:160',
            'phase'    => 'nullable|in:proceso,final,diseno',
        ]);
        foreach ($request->file('photos') as $file) {
            $path = $file->store('contratos-fotos', 'public');
            ImageOptimizer::optimizeStored($path);
            $contract->photos()->create(['path' => $path, 'caption' => $data['caption'] ?? null, 'phase' => $data['phase'] ?? 'proceso']);
        }

        return $this->contract($contract->fresh());
    }

    public function addStudent(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'document' => 'required|string|max:40',
            'name'     => 'required|string|max:160',
            'gender'   => 'nullable|in:hombre,mujer',
            'nickname' => 'nullable|string|max:60',
            'phone'    => 'nullable|string|max:20',
            'size'     => 'nullable|string|max:10',
            'combo'    => ['nullable', Rule::in(array_keys(Student::COMBOS))],
        ]);
        $data['document'] = preg_replace('/\D/', '', $data['document']);
        if ($contract->students()->where('document', $data['document'])->exists()) {
            return response()->json(['message' => 'Ese documento ya está en este contrato.'], 422);
        }
        $data['combo'] = $data['combo'] ?? $contract->defaultCombo();
        $s = $contract->students()->create($data);

        return response()->json($this->studentRow($s), 201);
    }

    /** Medidas de un alumno. Sirve también para sincronizar lo que se tomó sin internet. */
    public function measures(Request $request, Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);
        $data = $request->validate([
            'size'          => 'nullable|string|max:10',
            'chest'         => 'nullable|numeric|min:0|max:300',
            'waist'         => 'nullable|numeric|min:0|max:300',
            'hip'           => 'nullable|numeric|min:0|max:300',
            'sleeve_length' => 'nullable|numeric|min:0|max:200',
            'talle_length'  => 'nullable|numeric|min:0|max:200',
            'gender'        => 'nullable|in:hombre,mujer',
            'nickname'      => 'nullable|string|max:60',
            'phone'         => 'nullable|string|max:20',
            'combo'         => ['nullable', Rule::in(array_keys(Student::COMBOS))],
        ]);
        if (isset($data['size'])) {
            $data['size'] = mb_strtoupper(trim($data['size']));
        }
        $student->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));

        return response()->json($this->studentRow($student->fresh()));
    }

    /* ============================ Pagos ============================= */

    public function payments()
    {
        $students = Student::with('contract.school')->where('payment_status', 'en_revision')->orderBy('payment_declared_at')->get();
        $deposits = Contract::with('school')->whereNotNull('deposit_declared_at')->get()->filter(fn ($c) => $c->depositStatus() === 'en_revision');

        return response()->json([
            'students' => $students->map(fn ($s) => $this->studentRow($s) + [
                'school' => $s->contract?->school?->name, 'contract_id' => $s->contract_id,
                'amount_due' => (float) ($s->amount ?? $s->contract?->price_per_unit ?? 0),
            ])->values(),
            'deposits' => $deposits->map(fn ($c) => ['contract_id' => $c->id, 'school' => $c->school?->name, 'code' => $c->code] + $this->depositRow($c))->values(),
        ]);
    }

    public function approvePayment(Student $student)
    {
        $student->update(['payment_status' => 'pagado', 'paid_at' => now()]);

        return response()->json($this->studentRow($student->fresh()));
    }

    public function rejectPayment(Student $student)
    {
        $student->update(['payment_status' => 'pendiente', 'payment_method' => null, 'payment_proof' => null, 'payment_declared_at' => null]);

        return response()->json($this->studentRow($student->fresh()));
    }

    public function deposit(Request $request, Contract $contract)
    {
        $data = $request->validate(['action' => 'required|in:aprobar,rechazar,pedir', 'amount' => 'nullable|numeric|min:0', 'method' => 'nullable|string|max:60']);
        match ($data['action']) {
            'aprobar'  => $contract->update(['deposit_paid' => $data['amount'] ?? $contract->deposit_amount, 'deposit_paid_at' => now(), 'deposit_method' => $data['method'] ?? 'Transferencia (app)']),
            'rechazar' => $contract->update(['deposit_declared_at' => null]),
            'pedir'    => $contract->update(['deposit_amount' => $data['amount'] ?? 0]),
        };

        return response()->json($this->depositRow($contract->fresh()));
    }

    public function cartera()
    {
        $rows = app(SalesController::class)->carteraRows(false);

        return response()->json([
            'totals' => ['total' => $rows->sum('total'), 'paid' => $rows->sum('paid'), 'balance' => $rows->sum('balance'), 'review' => $rows->sum('review')],
            'rows'   => $rows->map(fn ($r) => collect($r)->except('contract')->all() + ['contract_id' => $r['contract']->id])->values(),
        ]);
    }

    /* ========================== Formatos =========================== */

    private function quoteRow(Quote $q): array
    {
        return [
            'id' => $q->id, 'code' => $q->code(), 'customer_name' => $q->customer_name, 'customer_phone' => $q->customer_phone,
            'school' => $q->school, 'status' => $q->status, 'status_label' => $q->statusLabel(), 'quantity' => (int) $q->quantity,
            'total' => $q->totalValue(), 'follow_up_at' => $q->follow_up_at?->toDateString(),
            'viewed_at' => $q->viewed_at?->toIso8601String(), 'created_at' => $q->created_at?->toIso8601String(),
            'kind' => $q->isBusiness() ? 'empresa' : ($q->isSchoolCustom() ? 'personalizada' : 'colegio'),
        ];
    }

    private function quoteDetail(Quote $q): array
    {
        $summary = $q->summary();

        return $this->quoteRow($q) + [
            'customer_email' => $q->customer_email,
            'notes'          => $q->notes,
            'client'         => $q->client(),
            'lines'          => $summary['lines'] ?? [],
            'unit_total'     => (int) ($summary['unit_total'] ?? 0),
            'items'          => $q->items,
            'gift_jackets'   => $q->giftJackets(),
            'public_url'     => $q->publicUrl(),
            'whatsapp_url'   => $q->whatsappToCustomerUrl(),
            'school_id'      => $q->school_id,
            'contract_id'    => $q->contract()?->id,
            'activities'     => $q->activities()->latest()->limit(20)->get()->map(fn ($a) => ['type' => $a->type, 'text' => $a->description, 'at' => $a->created_at?->toIso8601String()]),
        ];
    }

    private function contractRow(Contract $c): array
    {
        $students = $c->students;

        return [
            'id' => $c->id, 'code' => $c->code, 'school' => $c->school?->name, 'year' => $c->year,
            'stage' => $c->production_status, 'stage_label' => $c->stageLabel(),
            'students' => $students->count(),
            'measured' => $students->filter(fn ($s) => $s->hasMeasurements())->count(),
            'paid'     => $students->where('payment_status', 'pagado')->count(),
            'delivery_date' => $c->deliveryDate()?->toDateString(),
            'signed' => $c->isSigned(),
            'deposit_status' => $c->depositStatus(),
        ];
    }

    public static function studentRowStatic(Student $s): array
    {
        return [
            'id' => $s->id, 'document' => $s->document, 'name' => $s->name, 'nickname' => $s->nickname, 'phone' => $s->phone,
            'gender' => $s->gender, 'combo' => $s->combo, 'size' => $s->size,
            'chest' => $s->chest !== null ? (float) $s->chest : null, 'waist' => $s->waist !== null ? (float) $s->waist : null,
            'hip' => $s->hip !== null ? (float) $s->hip : null, 'sleeve_length' => $s->sleeve_length !== null ? (float) $s->sleeve_length : null,
            'talle_length' => $s->talle_length !== null ? (float) $s->talle_length : null,
            'measured' => $s->hasMeasurements(),
            'payment_status' => $s->payment_status ?: 'pendiente', 'payment_method' => $s->payment_method,
            'payment_proof_url' => $s->payment_proof ? Storage::disk('public')->url($s->payment_proof) : null,
        ];
    }

    private function studentRow(Student $s): array
    {
        return self::studentRowStatic($s);
    }

    public static function depositRow(Contract $c): array
    {
        return [
            'amount' => (float) $c->deposit_amount, 'paid' => (float) $c->deposit_paid, 'due' => $c->depositDue(),
            'status' => $c->depositStatus(), 'label' => $c->depositLabel(),
            'proof_url' => $c->deposit_proof ? Storage::disk('public')->url($c->deposit_proof) : null,
            'declared_at' => $c->deposit_declared_at?->toIso8601String(), 'paid_at' => $c->deposit_paid_at?->toIso8601String(),
        ];
    }
}

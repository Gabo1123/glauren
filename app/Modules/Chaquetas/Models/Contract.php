<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    /** Etapas de fabricación en orden */
    public const STAGES = [
        'cotizado'         => 'Cotizado',
        'aprobado'         => 'Aprobado',
        'toma_medidas'     => 'Toma de medidas',
        'en_produccion'    => 'En producción',
        'control_calidad'  => 'Control de calidad',
        'listo'            => 'Listo',
        'entregado'        => 'Entregado',
    ];

    protected $fillable = [
        'school_id', 'jacket_type_id', 'code', 'year', 'production_status', 'price_per_unit',
        'terms', 'contract_status', 'sign_token', 'signed_by_name', 'signed_by_document',
        'signature_path', 'sent_at', 'signed_at', 'notes', 'payment_reminder_at',
        'voting_enabled', 'voting_show_results',
        'design_approved_at', 'join_token', 'delivery_date', 'default_combo', 'materials',
        'objeto', 'product_details', 'combo_value', 'contract_total', 'total_words', 'delivery_business_days',
        'deposit_amount', 'deposit_paid', 'deposit_paid_at', 'deposit_method', 'deposit_proof', 'deposit_declared_at',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit'      => 'decimal:2',
            'sent_at'             => 'datetime',
            'signed_at'           => 'datetime',
            'payment_reminder_at' => 'datetime',
            'voting_enabled'      => 'boolean',
            'voting_show_results' => 'boolean',
            'materials'           => 'array',
            'deposit_paid_at'     => 'datetime',
            'deposit_declared_at' => 'datetime',
        ];
    }

    /**
     * Materiales de una prenda para ESTE contrato.
     * Usa los propios del contrato; si no, los del modelo de chaqueta; si no, los por defecto.
     */
    public function materialsFor(string $garment): array
    {
        $own = data_get($this->materials, $garment);
        if (is_array($own) && count($own)) {
            return $own;
        }
        if ($this->jacketType) {
            return $this->jacketType->componentsFor($garment);
        }

        return \App\Modules\Chaquetas\Models\JacketType::defaultsFor($garment);
    }

    protected static function booted(): void
    {
        // Cuando cambia la etapa de producción, la IA avisa sola por WhatsApp
        static::updated(function (Contract $contract) {
            if ($contract->wasChanged('production_status')) {
                try {
                    app(\App\Modules\Chaquetas\Services\OrderNotifier::class)->notifyStage($contract);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('OrderNotifier error', ['msg' => $e->getMessage()]);
                }
                // Al entregar: encuesta de satisfacción + reseña en Google al contacto del colegio
                if ($contract->production_status === 'entregado') {
                    try {
                        \App\Modules\Chaquetas\Support\DeliverySurvey::sendToSchool($contract);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('DeliverySurvey error', ['msg' => $e->getMessage()]);
                    }
                }
            }
        });
    }

    public function studentsTotal(): int
    {
        return $this->students()->count();
    }

    public function studentsPaidCount(): int
    {
        return $this->students()->where('payment_status', 'pagado')->count();
    }

    public function studentsPendingCount(): int
    {
        return $this->students()->where(function ($q) {
            $q->where('payment_status', '!=', 'pagado')->orWhereNull('payment_status');
        })->count();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function jacketType(): BelongsTo
    {
        return $this->belongsTo(JacketType::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ContractPhoto::class);
    }

    public function signers(): HasMany
    {
        return $this->hasMany(ContractSigner::class)->orderBy('sort')->orderBy('id');
    }

    public function priceChanges(): HasMany
    {
        return $this->hasMany(ContractChange::class)->latest();
    }

    /** Token para el registro por QR; se genera y guarda la primera vez. */
    public function joinToken(): string
    {
        if (! $this->join_token) {
            $this->join_token = \Illuminate\Support\Str::random(24);
            $this->save();
        }

        return $this->join_token;
    }

    /** Valor total del contrato (precio unitario × estudiantes) + cambios aprobados. */
    public function totalValue(): float
    {
        $unit = (float) ($this->price_per_unit ?? 0);
        $base = $this->students->sum(fn ($s) => (float) ($s->amount ?? $unit));
        $delta = (float) $this->priceChanges->where('status', 'aprobado')->sum('amount_delta');

        return $base + $delta;
    }

    public function paidValue(): float
    {
        $unit = (float) ($this->price_per_unit ?? 0);

        return (float) $this->students->where('payment_status', 'pagado')->sum(fn ($s) => (float) ($s->amount ?? $unit));
    }

    /** Contrato final: plantilla con los datos rellenados. */
    /**
     * Contrato listo para mostrar en HTML: si el texto trae formato (párrafos, negritas…)
     * se respeta; si es texto plano se convierten los saltos de línea. Siempre sin scripts.
     */
    public function renderedContractHtml(): string
    {
        $text = $this->renderedContract();
        if ($text === strip_tags($text)) {
            return nl2br(e($text));
        }
        $html = strip_tags($text, '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><table><thead><tbody><tr><td><th><span><div><hr>');
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        return (string) preg_replace('/javascript\s*:/i', '', $html);
    }

    public function renderedContract(): string
    {
        $tpl = Setting::get('contract_template', '');
        if (! $tpl) {
            return (string) $this->terms;
        }
        $money = fn ($v) => $v ? '$'.number_format((float) $v, 0, ',', '.').' pesos colombianos' : '[valor]';

        // Lista de contratantes desde los firmantes
        $contratantes = $this->signers->map(function ($s) {
            $doc = $s->signed_document ?: '__________';

            return '• Yo '.$s->name.' con cédula '.$doc.' de Bogotá en representación del Grado 11 para la promoción '
                .$this->year.' del '.($this->school?->name ?? 'Colegio')
                .', quien para todos los efectos del presente contrato se llamará EL CONTRATANTE.';
        })->implode("\n");
        if ($contratantes === '') {
            $contratantes = '• [Acudiente(s) contratante(s)]';
        }

        $repl = [
            '{codigo}'         => $this->code,
            '{fecha}'          => now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            '{contratantes}'   => $contratantes,
            '{colegio}'        => $this->school?->name ?? '',
            '{promo}'          => $this->year,
            '{objeto}'         => $this->objeto ?: '[Objeto: cantidad y tipo de prendas]',
            '{detalle}'        => $this->product_details ?: '[Detalle y bordados de las prendas]',
            '{valor_combo}'    => $money($this->combo_value ?: $this->price_per_unit),
            '{valor_total}'    => $money($this->contract_total ?: $this->totalValue()),
            '{valor_letras}'   => $this->total_words ?: '[valor en letras]',
            '{anticipo}'       => $this->hasDeposit() ? $money($this->deposit_amount) : '[anticipo]',
            '{anticipo_letras}' => $this->hasDeposit() ? \App\Modules\Chaquetas\Support\NumberToWords::pesos((float) $this->deposit_amount) : '[anticipo en letras]',
            '{contratista}'    => Setting::get('contratista_nombre', Setting::get('brand_name', 'Glauren')),
            '{nit}'            => Setting::get('contratista_nit', ''),
            '{cuenta}'         => Setting::get('contratista_cuenta', ''),
            '{cuenta_direccion}' => Setting::get('contact_address', 'Bogotá, Colombia'),
            '{correo}'         => Setting::get('contact_email', 'glauren@chaquetasdeprom.com'),
        ];

        return strtr($tpl, $repl);
    }

    /* ===================== Anticipo del colegio ===================== */

    public function hasDeposit(): bool
    {
        return (float) $this->deposit_amount > 0;
    }

    /** pendiente | en_revision | parcial | pagado | (null si no hay anticipo) */
    public function depositStatus(): ?string
    {
        if (! $this->hasDeposit()) {
            return null;
        }
        $paid = (float) $this->deposit_paid;
        if ($paid >= (float) $this->deposit_amount) {
            return 'pagado';
        }
        if ($this->deposit_declared_at && (! $this->deposit_paid_at || $this->deposit_declared_at->gt($this->deposit_paid_at))) {
            return 'en_revision';
        }

        return $paid > 0 ? 'parcial' : 'pendiente';
    }

    public function depositLabel(): string
    {
        return ['pagado' => 'Anticipo pagado', 'en_revision' => 'Comprobante por revisar', 'parcial' => 'Anticipo parcial', 'pendiente' => 'Anticipo pendiente'][$this->depositStatus()] ?? 'Sin anticipo';
    }

    public function depositDue(): float
    {
        return max(0, (float) $this->deposit_amount - (float) $this->deposit_paid);
    }

    /** Combo por defecto del contrato (lo heredan los alumnos nuevos). */
    public function defaultCombo(): string
    {
        return $this->default_combo ?: 'chaqueta';
    }

    /** ¿El contrato ya está firmado? (cierra la lista de alumnos) */
    public function isSigned(): bool
    {
        return $this->contract_status === 'firmado' || $this->signed_at !== null;
    }

    /** ¿Todos los firmantes registrados ya firmaron? */
    public function allSigned(): bool
    {
        $signers = $this->signers;

        return $signers->count() > 0 && $signers->every(fn ($s) => $s->hasSigned());
    }

    /** ¿Todos los estudiantes ya pagaron? (o el colegio paga todo) */
    public function allStudentsPaid(): bool
    {
        if ($this->school?->schoolPays()) {
            return true;
        }
        $students = $this->students;

        return $students->count() > 0 && $students->every(fn ($s) => $s->payment_status === 'pagado');
    }

    /**
     * Fecha de entrega. Usa la fecha manual si existe; si no, calcula
     * los días hábiles colombianos (con festivos) desde la firma del contrato.
     */
    public function deliveryDate(): ?\Carbon\Carbon
    {
        if ($this->delivery_date) {
            return $this->delivery_date instanceof \Carbon\Carbon
                ? $this->delivery_date
                : \Carbon\Carbon::parse($this->delivery_date);
        }
        $start = $this->signed_at ?? $this->created_at;
        if (! $start) {
            return null;
        }
        $days = (int) ($this->delivery_business_days ?: 35);

        return \App\Modules\Chaquetas\Support\ColombiaHolidays::addBusinessDays($start->copy(), $days);
    }

    /** Índice de la etapa actual (para dibujar la línea de tiempo) */
    public function stageIndex(): int
    {
        return array_search($this->production_status, array_keys(self::STAGES), true) ?: 0;
    }

    public function stageLabel(): string
    {
        return self::STAGES[$this->production_status] ?? $this->production_status;
    }
}

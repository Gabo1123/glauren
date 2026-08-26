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
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit'      => 'decimal:2',
            'sent_at'             => 'datetime',
            'signed_at'           => 'datetime',
            'payment_reminder_at' => 'datetime',
        ];
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

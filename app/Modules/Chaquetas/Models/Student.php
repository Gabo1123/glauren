<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $hidden = ['pin'];

    /** Prendas disponibles y las medidas (cm) que pide cada una. */
    public const GARMENTS = [
        'chaqueta' => [
            'label'  => 'Chaqueta',
            'icon'   => '🧥',
            // La chaqueta usa las columnas base del alumno.
            'fields' => [
                'chest'         => 'Contorno de pecho',
                'waist'         => 'Contorno de cintura',
                'hip'           => 'Contorno de cadera',
                'sleeve_length' => 'Largo de manga',
                'talle_length'  => 'Largo de talle',
            ],
            'base' => true,
        ],
        'buzo' => [
            'label'  => 'Buzo',
            'icon'   => '👕',
            'fields' => [
                'chest'  => 'Contorno de pecho',
                'waist'  => 'Contorno de cintura',
                'length' => 'Largo total',
                'sleeve' => 'Largo de manga',
            ],
        ],
        'polo' => [
            'label'  => 'Polo',
            'icon'   => '🎽',
            'fields' => [
                'chest'  => 'Contorno de pecho',
                'length' => 'Largo total',
                'sleeve' => 'Largo de manga (corta)',
            ],
        ],
        'gorra' => [
            'label'  => 'Gorra',
            'icon'   => '🧢',
            'fields' => [
                'head' => 'Contorno de cabeza',
            ],
        ],
    ];

    /** Combos que el asesor puede escoger (por contrato o por alumno). */
    public const COMBOS = [
        'chaqueta'                 => ['label' => 'Solo chaqueta',                  'garments' => ['chaqueta']],
        'buzo'                     => ['label' => 'Solo buzo',                      'garments' => ['buzo']],
        'chaqueta_gorra'           => ['label' => 'Chaqueta + Gorra',              'garments' => ['chaqueta', 'gorra']],
        'chaqueta_buzo'            => ['label' => 'Chaqueta + Buzo',               'garments' => ['chaqueta', 'buzo']],
        'chaqueta_polo'            => ['label' => 'Chaqueta + Polo',               'garments' => ['chaqueta', 'polo']],
        'chaqueta_buzo_gorra'      => ['label' => 'Chaqueta + Buzo + Gorra',       'garments' => ['chaqueta', 'buzo', 'gorra']],
        'chaqueta_buzo_polo'       => ['label' => 'Chaqueta + Buzo + Polo',        'garments' => ['chaqueta', 'buzo', 'polo']],
        'chaqueta_buzo_polo_gorra' => ['label' => 'Chaqueta + Buzo + Polo + Gorra', 'garments' => ['chaqueta', 'buzo', 'polo', 'gorra']],
    ];

    /** Lista de prendas del combo elegido (por defecto, chaqueta). */
    public function garments(): array
    {
        $combo = $this->combo ?: 'chaqueta';

        return self::COMBOS[$combo]['garments'] ?? ['chaqueta'];
    }

    public function comboLabel(): string
    {
        return self::COMBOS[$this->combo ?? '']['label'] ?? 'Solo chaqueta';
    }

    /** Medida de una prenda extra (buzo/polo) guardada en el JSON measurements. */
    public function measure(string $garment, string $field): ?string
    {
        return data_get($this->measurements, "$garment.$field");
    }

    public function posts(): HasMany
    {
        return $this->hasMany(StudentPost::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    protected $fillable = [
        'contract_id', 'document', 'phone', 'pin', 'photo', 'bio', 'notified_stage', 'notify_optin', 'name', 'gender', 'nickname',
        'size', 'combo', 'measurements', 'talle_length', 'sleeve_length', 'chest', 'waist', 'hip',
        'payment_status', 'amount', 'rapyd_checkout_id', 'paid_at',
        'payment_method', 'payment_proof', 'payment_declared_at',
    ];

    protected function casts(): array
    {
        return [
            'talle_length'  => 'decimal:1',
            'sleeve_length' => 'decimal:1',
            'chest'         => 'decimal:1',
            'waist'         => 'decimal:1',
            'hip'           => 'decimal:1',
            'amount'        => 'decimal:2',
            'measurements'  => 'array',
            'paid_at'       => 'datetime',
            'payment_declared_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'pagado';
    }

    /** El estudiante declaró un pago y espera aprobación del admin. */
    public function isUnderReview(): bool
    {
        return $this->payment_status === 'en_revision';
    }

    public function proofUrl(): ?string
    {
        return $this->payment_proof ? Storage::disk('public')->url($this->payment_proof) : null;
    }

    public function methodLabel(): string
    {
        return [
            'efectivo'    => 'Efectivo (día de entrega)',
            'nequi'       => 'Nequi',
            'bancolombia' => 'Bancolombia',
        ][$this->payment_method] ?? '—';
    }

    public function hasMeasurements(): bool
    {
        return $this->size || $this->chest || $this->waist || $this->hip;
    }
}

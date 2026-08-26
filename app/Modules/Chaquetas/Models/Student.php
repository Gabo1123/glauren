<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $hidden = ['pin'];

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
        'size', 'talle_length', 'sleeve_length', 'chest', 'waist', 'hip',
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

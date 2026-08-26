<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractSigner extends Model
{
    protected $fillable = [
        'contract_id', 'name', 'role', 'phone', 'email', 'token',
        'otp', 'otp_expires', 'signed_document', 'signature_path', 'signed_at', 'sort',
    ];

    protected function casts(): array
    {
        return ['otp_expires' => 'datetime', 'signed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (ContractSigner $s) {
            if (! $s->token) {
                $s->token = Str::random(48);
            }
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function hasSigned(): bool
    {
        return (bool) $this->signed_at;
    }

    public function signatureUrl(): ?string
    {
        return $this->signature_path ? Storage::disk('public')->url($this->signature_path) : null;
    }

    public function signUrl(): string
    {
        return url('/firmar/'.$this->token);
    }
}

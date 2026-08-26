<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    public const CATEGORIES = [
        'tela'       => 'Tela',
        'insumos'    => 'Insumos',
        'mano_obra'  => 'Mano de obra',
        'transporte' => 'Transporte',
        'servicios'  => 'Servicios',
        'otros'      => 'Otros',
    ];

    protected $fillable = [
        'date', 'category', 'concept', 'supplier', 'amount', 'invoice_path',
        'school_id', 'contract_id', 'jacket_type_id', 'material', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function jacketType(): BelongsTo
    {
        return $this->belongsTo(JacketType::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function invoiceUrl(): ?string
    {
        return $this->invoice_path ? Storage::disk('public')->url($this->invoice_path) : null;
    }

    public function invoiceIsPdf(): bool
    {
        return $this->invoice_path && str_ends_with(strtolower($this->invoice_path), '.pdf');
    }
}

<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractChange extends Model
{
    protected $fillable = ['contract_id', 'title', 'description', 'amount_delta', 'status', 'decided_at'];

    protected function casts(): array
    {
        return ['amount_delta' => 'decimal:2', 'decided_at' => 'datetime'];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}

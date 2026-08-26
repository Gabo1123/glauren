<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractMessage extends Model
{
    protected $fillable = ['contract_id', 'sender', 'body', 'read'];

    protected function casts(): array
    {
        return ['read' => 'boolean'];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}

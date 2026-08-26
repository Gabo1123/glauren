<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContractPhoto extends Model
{
    protected $fillable = ['contract_id', 'path', 'caption', 'phase'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}

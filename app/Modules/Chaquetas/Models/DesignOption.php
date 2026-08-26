<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class DesignOption extends Model
{
    protected $fillable = ['contract_id', 'label', 'image_path', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DesignVote::class);
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}

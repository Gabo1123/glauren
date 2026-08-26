<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CollectionImage extends Model
{
    protected $fillable = ['collection_id', 'path', 'caption', 'sort'];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function url(): string
    {
        return str_starts_with($this->path, 'http')
            ? $this->path
            : Storage::disk('public')->url($this->path);
    }
}

<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PhotoService extends Model
{
    protected $fillable = ['photo_category_id', 'title', 'subtitle', 'description', 'keywords', 'image_path', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PhotoCategory::class, 'photo_category_id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}

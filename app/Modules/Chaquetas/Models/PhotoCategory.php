<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PhotoCategory extends Model
{
    protected $fillable = ['name', 'slug', 'subtitle', 'description', 'keywords', 'icon', 'image_path', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function services(): HasMany
    {
        return $this->hasMany(PhotoService::class)->orderBy('sort')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}

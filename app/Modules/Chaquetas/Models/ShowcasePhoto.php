<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ShowcasePhoto extends Model
{
    protected $fillable = ['image_path', 'label', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function imageUrl(): string
    {
        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path);
    }
}

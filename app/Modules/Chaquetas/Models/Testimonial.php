<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    protected $fillable = ['text', 'name', 'role', 'photo_path', 'rating', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'rating' => 'integer'];
    }

    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return str_starts_with($this->photo_path, 'http')
            ? $this->photo_path
            : Storage::disk('public')->url($this->photo_path);
    }
}

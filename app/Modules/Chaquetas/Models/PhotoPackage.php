<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoPackage extends Model
{
    protected $fillable = ['name', 'tagline', 'features', 'featured', 'sort', 'active'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'featured' => 'boolean',
            'active'   => 'boolean',
        ];
    }
}

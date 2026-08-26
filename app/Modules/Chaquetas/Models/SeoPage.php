<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = ['page_key', 'label', 'title', 'description', 'keywords', 'og_image', 'noindex'];

    protected function casts(): array
    {
        return ['noindex' => 'boolean'];
    }

    public static function for(string $key): ?self
    {
        return static::where('page_key', $key)->first();
    }
}

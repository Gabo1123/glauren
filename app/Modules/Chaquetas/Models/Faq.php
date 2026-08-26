<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'page_key', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public static function published(string $pageKey = 'home')
    {
        return static::where('active', true)->where('page_key', $pageKey)->orderBy('sort')->get();
    }
}

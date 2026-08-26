<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class RankingEntry extends Model
{
    protected $fillable = ['name', 'city', 'slug', 'votes', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }
}

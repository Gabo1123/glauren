<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = ['label', 'url', 'parent_id', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort');
    }

    /** Menú raíz activo, ordenado, con hijos */
    public static function tree()
    {
        return static::query()
            ->whereNull('parent_id')
            ->where('active', true)
            ->with('children')
            ->orderBy('sort')
            ->get();
    }
}

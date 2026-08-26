<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class JacketType extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'category', 'components',
        'model_path', 'default_view', 'base_price', 'patch_price', 'active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'base_price'  => 'decimal:2',
            'patch_price' => 'decimal:2',
            'active'      => 'boolean',
            'components'  => 'array',
        ];
    }

    /**
     * Consumo de materiales por chaqueta si el tipo aún no tiene lista propia.
     * unit: 'm' (metros) o 'u' (unidades). Las telas se calculan con % de desperdicio.
     */
    public const DEFAULT_COMPONENTS = [
        ['name' => 'Tela cuerpo (paño)', 'qty' => 1.2, 'unit' => 'm', 'cost' => 18000],
        ['name' => 'Tela mangas', 'qty' => 0.8, 'unit' => 'm', 'cost' => 22000],
        ['name' => 'Forro', 'qty' => 1.0, 'unit' => 'm', 'cost' => 8000],
        ['name' => 'Rib (pretina y puños)', 'qty' => 0.4, 'unit' => 'm', 'cost' => 12000],
        ['name' => 'Ribete', 'qty' => 1.5, 'unit' => 'm', 'cost' => 3000],
        ['name' => 'Entretela', 'qty' => 0.6, 'unit' => 'm', 'cost' => 5000],
        ['name' => 'Botones', 'qty' => 5, 'unit' => 'u', 'cost' => 500],
        ['name' => 'Cremallera', 'qty' => 1, 'unit' => 'u', 'cost' => 2500],
        ['name' => 'Escudo / bordado', 'qty' => 1, 'unit' => 'u', 'cost' => 6000],
        ['name' => 'Capota (tela)', 'qty' => 0, 'unit' => 'm', 'cost' => 18000],
        ['name' => 'Forro de capota', 'qty' => 0, 'unit' => 'm', 'cost' => 8000],
    ];

    /** Lista de componentes efectiva (propia o la de por defecto). */
    public function componentList(): array
    {
        $c = $this->components;

        return (is_array($c) && count($c)) ? $c : self::DEFAULT_COMPONENTS;
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function hasModel(): bool
    {
        return $this->model_path && Storage::disk('public')->exists($this->model_path);
    }

    public function modelUrl(): ?string
    {
        return $this->model_path ? Storage::disk('public')->url($this->model_path) : null;
    }
}

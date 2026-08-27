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

    /** Materiales por defecto del buzo. */
    public const DEFAULT_COMPONENTS_BUZO = [
        ['name' => 'Tela cuerpo (percha/algodón)', 'qty' => 1.4, 'unit' => 'm', 'cost' => 16000],
        ['name' => 'Rib (puños y pretina)', 'qty' => 0.4, 'unit' => 'm', 'cost' => 12000],
        ['name' => 'Capota (tela)', 'qty' => 0.8, 'unit' => 'm', 'cost' => 16000],
        ['name' => 'Cordón capota', 'qty' => 1, 'unit' => 'u', 'cost' => 1500],
        ['name' => 'Escudo / bordado', 'qty' => 1, 'unit' => 'u', 'cost' => 6000],
    ];

    /** Materiales por defecto del polo. */
    public const DEFAULT_COMPONENTS_POLO = [
        ['name' => 'Tela piqué', 'qty' => 1.0, 'unit' => 'm', 'cost' => 14000],
        ['name' => 'Cuello tejido', 'qty' => 1, 'unit' => 'u', 'cost' => 3500],
        ['name' => 'Botones', 'qty' => 3, 'unit' => 'u', 'cost' => 400],
        ['name' => 'Escudo / bordado', 'qty' => 1, 'unit' => 'u', 'cost' => 6000],
    ];

    /** Materiales por defecto de la gorra. */
    public const DEFAULT_COMPONENTS_GORRA = [
        ['name' => 'Tela (paño/gabardina)', 'qty' => 0.3, 'unit' => 'm', 'cost' => 14000],
        ['name' => 'Broche / hebilla ajuste', 'qty' => 1, 'unit' => 'u', 'cost' => 1200],
        ['name' => 'Escudo / bordado', 'qty' => 1, 'unit' => 'u', 'cost' => 6000],
    ];

    /** Materiales por defecto de una prenda. */
    public static function defaultsFor(string $garment): array
    {
        return match ($garment) {
            'buzo'  => self::DEFAULT_COMPONENTS_BUZO,
            'polo'  => self::DEFAULT_COMPONENTS_POLO,
            'gorra' => self::DEFAULT_COMPONENTS_GORRA,
            default => self::DEFAULT_COMPONENTS,
        };
    }

    /**
     * Lista de materiales de una prenda (chaqueta/buzo/polo).
     * Soporta el formato nuevo por prenda y el viejo (lista plana = chaqueta).
     */
    public function componentsFor(string $garment): array
    {
        $c = $this->components;

        // Formato nuevo: {chaqueta:[...], buzo:[...], polo:[...], gorra:[...]}
        if (is_array($c) && (isset($c['chaqueta']) || isset($c['buzo']) || isset($c['polo']) || isset($c['gorra']))) {
            $list = $c[$garment] ?? null;
            if (is_array($list) && count($list)) {
                return $list;
            }
        }
        // Formato viejo: lista plana → corresponde a la chaqueta
        elseif ($garment === 'chaqueta' && is_array($c) && count($c) && isset($c[0])) {
            return $c;
        }

        return self::defaultsFor($garment);
    }

    /** Lista de componentes efectiva de la chaqueta (compatibilidad). */
    public function componentList(): array
    {
        return $this->componentsFor('chaqueta');
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

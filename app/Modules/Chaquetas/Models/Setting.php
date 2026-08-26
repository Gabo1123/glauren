<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'site_settings';
    protected $fillable = ['key', 'value'];

    /** Caché en memoria por request */
    protected static array $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key] ?? $default;
        }
        $val = static::query()->where('key', $key)->value('value');
        static::$cache[$key] = $val;

        return $val ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache[$key] = $value;
    }

    /** Todas como colección clave=>valor */
    public static function map(): \Illuminate\Support\Collection
    {
        return static::query()->pluck('value', 'key');
    }
}

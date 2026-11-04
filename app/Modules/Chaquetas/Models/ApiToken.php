<?php

namespace App\Modules\Chaquetas\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Token de sesión de la app móvil. En la base solo se guarda el hash. */
class ApiToken extends Model
{
    protected $fillable = ['owner_type', 'owner_id', 'name', 'token', 'last_used_at', 'expires_at'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    /** Crea un token y devuelve el texto plano (solo se ve una vez). */
    public static function issue(User|Student $owner, ?string $device = null): string
    {
        $plain = Str::random(60);
        static::create([
            'owner_type' => $owner instanceof User ? 'user' : 'student',
            'owner_id'   => $owner->getKey(),
            'name'       => $device ? mb_substr($device, 0, 120) : null,
            'token'      => hash('sha256', $plain),
            'expires_at' => now()->addDays(90),
        ]);

        return $plain;
    }

    public static function findPlain(?string $plain): ?self
    {
        if (! $plain) {
            return null;
        }
        $t = static::where('token', hash('sha256', $plain))->first();

        return $t && (! $t->expires_at || $t->expires_at->isFuture()) ? $t : null;
    }

    public function owner(): User|Student|null
    {
        return $this->owner_type === 'user' ? User::find($this->owner_id) : Student::find($this->owner_id);
    }
}

<?php

namespace App\Modules\Chaquetas\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name', 'slug', 'city', 'contact_name', 'contact_phone', 'contact_email', 'user_id', 'active', 'payment_mode',
    ];

    /** ¿El colegio paga todo (los estudiantes no pagan en línea)? */
    public function schoolPays(): bool
    {
        return $this->payment_mode === 'colegio';
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Al crear un colegio, entra automáticamente al ranking público
        static::created(function (School $school) {
            RankingEntry::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($school->name)],
                ['name' => $school->name, 'city' => $school->city, 'votes' => 0, 'active' => true]
            );
        });
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

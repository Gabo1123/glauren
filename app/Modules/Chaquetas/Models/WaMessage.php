<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessage extends Model
{
    protected $fillable = ['phone', 'lead_id', 'direction', 'body', 'wa_id'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** Últimos mensajes de un número, en orden cronológico, para dar contexto a la IA. */
    public static function historyFor(string $phone, int $limit = 12): array
    {
        return static::where('phone', $phone)
            ->latest('id')->limit($limit)->get()
            ->reverse()
            ->map(fn ($m) => [
                'role' => $m->direction === 'in' ? 'user' : 'model',
                'text' => $m->body,
            ])->values()->all();
    }
}

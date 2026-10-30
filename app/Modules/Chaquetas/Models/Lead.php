<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'school', 'message', 'source', 'ip', 'jacket_type', 'accepted_terms', 'ai_paused', 'human_requested_at'];

    protected function casts(): array
    {
        return [
            'accepted_terms'     => 'boolean',
            'ai_paused'          => 'boolean',
            'human_requested_at' => 'datetime',
        ];
    }

    /** Leads que están esperando atención de un humano (IA pausada). */
    public function scopeNeedsHuman($query)
    {
        return $query->where('ai_paused', true);
    }

    /** Cotización/seguimiento comercial de este contacto. */
    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class)->latestOfMany();
    }
}

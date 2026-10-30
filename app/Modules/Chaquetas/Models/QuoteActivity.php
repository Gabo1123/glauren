<?php

namespace App\Modules\Chaquetas\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteActivity extends Model
{
    protected $fillable = ['quote_id', 'type', 'description', 'user_id'];

    /** Ícono (Bootstrap Icons) y color por tipo de evento. */
    public const TYPES = [
        'creada'   => ['bi-file-earmark-plus', '#8a8a92'],
        'whatsapp' => ['bi-whatsapp', '#25d366'],
        'correo'   => ['bi-envelope-check', '#FFC72C'],
        'vista'    => ['bi-eye', '#4f8cff'],
        'estado'   => ['bi-arrow-repeat', '#FF6B1A'],
        'nota'     => ['bi-chat-left-text', '#c9c9c9'],
        'pdf'      => ['bi-file-earmark-pdf', '#e0736b'],
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function icon(): string
    {
        return self::TYPES[$this->type][0] ?? 'bi-dot';
    }

    public function color(): string
    {
        return self::TYPES[$this->type][1] ?? '#8a8a92';
    }
}

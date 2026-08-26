<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Quote extends Model
{
    protected $fillable = [
        'jacket_type_id', 'customer_name', 'customer_phone', 'customer_email',
        'school', 'quantity', 'config', 'estimated_price', 'image_path', 'images', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'config'          => 'array',
            'images'          => 'array',
            'estimated_price' => 'decimal:2',
        ];
    }

    /** URLs de todas las vistas (frente, derecha, espalda, izquierda). */
    public function imageUrls(): array
    {
        $out = [];
        foreach ((array) $this->images as $label => $path) {
            if ($path) {
                $out[$label] = Storage::disk('public')->url($path);
            }
        }
        if (! $out && $this->image_path) {
            $out['frente'] = Storage::disk('public')->url($this->image_path);
        }

        return $out;
    }

    public function jacketType(): BelongsTo
    {
        return $this->belongsTo(JacketType::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'nuevo'      => 'primary',
            'contactado' => 'warning',
            'cerrado'    => 'success',
            'cancelado'  => 'secondary',
            default      => 'light',
        };
    }
}

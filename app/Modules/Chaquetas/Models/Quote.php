<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Quote extends Model
{
    /** Estados del embudo de ventas (clave => etiqueta). */
    public const STATUSES = [
        'nuevo'      => 'Nuevo',
        'contactado' => 'Contactado',
        'enviada'    => 'Cotización enviada',
        'vista'      => 'Vista por el cliente',
        'negociando' => 'En negociación',
        'cerrado'    => 'Ganada',
        'cancelado'  => 'Perdida',
    ];

    /** Estados que todavía requieren seguimiento. */
    public const OPEN_STATUSES = ['nuevo', 'contactado', 'enviada', 'vista', 'negociando'];

    protected $fillable = [
        'jacket_type_id', 'customer_name', 'customer_phone', 'customer_email',
        'school', 'quantity', 'config', 'items', 'estimated_price', 'image_path', 'images', 'status', 'notes',
        'source', 'sent_at', 'viewed_at', 'view_count', 'follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'config'          => 'array',
            'items'           => 'array',
            'images'          => 'array',
            'estimated_price' => 'decimal:2',
            'sent_at'         => 'datetime',
            'viewed_at'       => 'datetime',
            'follow_up_at'    => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Número consecutivo y enlace público para cada cotización nueva.
        static::creating(function (Quote $quote) {
            if (! $quote->number) {
                $start = (int) (Setting::get('quote_number_start', '65044') ?: 65044);
                $quote->number = max($start, (int) static::max('number') + 1);
            }
            if (! $quote->public_token) {
                $quote->public_token = Str::random(32);
            }
        });
    }

    public function code(): string
    {
        return 'COT-'.($this->number ?? $this->id);
    }

    public function publicUrl(): string
    {
        return route('cotizacion.show', $this->public_token);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function summary(): array
    {
        return QuoteCatalog::summarize($this->items);
    }

    /** Enlace de WhatsApp al cliente con el link de su cotización. */
    public function whatsappToCustomerUrl(): string
    {
        $phone = preg_replace('/\D/', '', (string) $this->customer_phone);
        if (strlen($phone) === 10) {
            $phone = '57'.$phone; // celular colombiano sin indicativo
        }
        $first = Str::of($this->customer_name)->explode(' ')->first();
        $msg = "¡Hola {$first}! 👋 Te comparto la cotización {$this->code()} de G'Lauren para tu promoción"
            .($this->school ? " de {$this->school}" : '').":\n".$this->publicUrl()
            ."\n\nCualquier duda me escribes por aquí. 🧥";

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($msg);
    }

    public function needsFollowUp(): bool
    {
        return $this->follow_up_at && $this->follow_up_at->lte(today()) && in_array($this->status, self::OPEN_STATUSES, true);
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
            'enviada'    => 'info',
            'vista'      => 'info',
            'negociando' => 'warning',
            'cerrado'    => 'success',
            'cancelado'  => 'secondary',
            default      => 'light',
        };
    }
}

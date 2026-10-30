<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use App\Modules\Chaquetas\Mail\QuoteMail;
use App\Modules\Chaquetas\Support\QuoteCatalog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        static::created(function (Quote $quote) {
            $quote->log('creada', $quote->source === 'manual' ? 'Cotización creada en el panel' : 'Cotización recibida desde la web (diseñador 3D)');
        });

        // Cada cambio de estado queda en el historial.
        static::updated(function (Quote $quote) {
            if ($quote->wasChanged('status')) {
                $quote->log('estado', 'Estado: '.(self::STATUSES[$quote->getOriginal('status')] ?? $quote->getOriginal('status')).' → '.$quote->statusLabel());
            }
        });
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QuoteActivity::class)->latest()->latest('id');
    }

    public function log(string $type, string $description): QuoteActivity
    {
        return $this->activities()->create([
            'type'        => $type,
            'description' => mb_substr($description, 0, 255),
            'user_id'     => Auth::id(),
        ]);
    }

    /** Otras cotizaciones del mismo cliente (mismo celular). */
    public function sameCustomer()
    {
        $digits = substr(preg_replace('/\D/', '', (string) $this->customer_phone), -10);
        if (strlen($digits) < 7) {
            return collect();
        }

        return static::where('id', '!=', $this->id)
            ->where('customer_phone', 'like', '%'.substr($digits, -4))
            ->latest()->take(50)->get()
            ->filter(fn ($q) => str_ends_with(preg_replace('/\D/', '', (string) $q->customer_phone), $digits))
            ->take(10)->values();
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

    /** Enlace de WhatsApp al cliente con el mensaje predeterminado. */
    public function whatsappToCustomerUrl(?string $message = null): string
    {
        return 'https://wa.me/'.$this->whatsappPhone().'?text='.rawurlencode($message ?? $this->whatsappMessage());
    }

    /**
     * Envía la cotización al correo del cliente. Devuelve true si salió.
     * Si falla el correo solo queda en el log.
     */
    public function emailToCustomer(): bool
    {
        if (! $this->customer_email || ! filter_var($this->customer_email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            Mail::to($this->customer_email)->send(new QuoteMail($this));
        } catch (\Throwable $e) {
            Log::warning('QuoteMail error', ['quote' => $this->id, 'msg' => $e->getMessage()]);

            return false;
        }
        $this->markSent();
        $this->log('correo', 'Cotización enviada al correo '.$this->customer_email);

        return true;
    }

    /** Marca como enviada y programa el seguimiento a 2 días. */
    public function markSent(): void
    {
        $this->sent_at = $this->sent_at ?? now();
        if (in_array($this->status, ['nuevo', 'contactado'], true)) {
            $this->status = 'enviada';
        }
        if (! $this->follow_up_at || $this->follow_up_at->lt(today()->addDays(2))) {
            $this->follow_up_at = today()->addDays(2);
        }
        $this->save();
    }

    /** Mensaje predeterminado de WhatsApp (editable en el panel). */
    public const DEFAULT_WA_MESSAGE = "¡Hola {nombre}! 👋 Te comparto la cotización {numero} de G'Lauren para la promoción{colegio}.\n\n"
        ."Ahí encuentras precios, combos con descuento y todo lo que incluye su prom:\n{enlace}\n\n"
        ."Te adjunto también el PDF. ¿Te parece si agendamos la toma de medidas? 🧥✨";

    public function whatsappMessage(?string $template = null): string
    {
        $template = $template ?: (Setting::get('quote_whatsapp_message') ?: self::DEFAULT_WA_MESSAGE);
        $first = (string) Str::of($this->customer_name)->explode(' ')->first();

        return strtr($template, [
            '{nombre}'   => $first,
            '{numero}'   => $this->code(),
            '{enlace}'   => $this->publicUrl(),
            '{colegio}'  => $this->school ? ' de '.$this->school : '',
            '{cantidad}' => (string) $this->quantity,
        ]);
    }

    public function whatsappPhone(): string
    {
        $phone = preg_replace('/\D/', '', (string) $this->customer_phone);

        return strlen($phone) === 10 ? '57'.$phone : $phone; // celular colombiano sin indicativo
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

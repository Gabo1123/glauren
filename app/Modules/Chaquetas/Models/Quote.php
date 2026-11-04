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
        'jacket_type_id', 'lead_id', 'school_id', 'customer_name', 'customer_phone', 'customer_email',
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
            $quote->log('creada', match ($quote->source) {
                'manual' => 'Cotización creada en el panel',
                'lead'   => 'Seguimiento iniciado desde Leads / Contactos',
                'empresa' => 'Cotización para empresa creada en el panel',
                'personalizada' => 'Cotización personalizada para colegio creada en el panel',
                default  => 'Cotización recibida desde la web (diseñador 3D)',
            });
        });

        // Cada cambio de estado queda en el historial.
        static::updated(function (Quote $quote) {
            if ($quote->wasChanged('status')) {
                $quote->log('estado', 'Estado: '.(self::STATUSES[$quote->getOriginal('status')] ?? $quote->getOriginal('status')).' → '.$quote->statusLabel());
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** Colegio creado cuando el cliente contrató (la columna `school` es solo el nombre escrito). */
    public function schoolRecord(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /** Cotización general (sin prendas ni valores): la que se envía a los leads. */
    public function isGeneral(): bool
    {
        return empty($this->summary()['lines']) && (float) $this->estimated_price <= 0;
    }

    /** Cotización del lead; la crea si aún no tiene. */
    public static function forLead(Lead $lead): self
    {
        return static::firstOrCreate(['lead_id' => $lead->id], [
            'customer_name'   => $lead->name ?: 'Cliente',
            'customer_phone'  => $lead->phone ?: '',
            'customer_email'  => $lead->email,
            'school'          => $lead->school,
            'quantity'        => 1,
            'estimated_price' => 0,
            'status'          => 'nuevo',
            'source'          => 'lead',
            'follow_up_at'    => today(),
        ]);
    }

    /** Todas las etapas del cliente, de la primera charla a la entrega. */
    public const JOURNEY = [
        'nuevo'           => ['Nuevo', 'bi-person-plus', 'venta'],
        'contactado'      => ['Contactado', 'bi-telephone', 'venta'],
        'enviada'         => ['Cotización enviada', 'bi-send', 'venta'],
        'vista'           => ['La vio', 'bi-eye', 'venta'],
        'negociando'      => ['Negociando', 'bi-chat-dots', 'venta'],
        'ganada'          => ['Ganada · colegio', 'bi-trophy', 'cierre'],
        'contrato'        => ['Contrato firmado', 'bi-pen', 'cierre'],
        'toma_medidas'    => ['Toma de medidas', 'bi-rulers', 'produccion'],
        'en_produccion'   => ['En producción', 'bi-scissors', 'produccion'],
        'control_calidad' => ['Control de calidad', 'bi-patch-check', 'produccion'],
        'listo'           => ['Listo', 'bi-box-seam', 'produccion'],
        'entregado'       => ['Entregado', 'bi-stars', 'produccion'],
    ];

    /** Etapas de producción: se guardan en el contrato (y avisan al colegio por WhatsApp). */
    public const PRODUCTION_STAGES = ['toma_medidas', 'en_produccion', 'control_calidad', 'listo', 'entregado'];

    /** Etapa actual del recorrido completo (clave de JOURNEY). */
    public function journeyStage(): string
    {
        $contract = $this->school_id ? $this->contract() : null;
        if ($contract) {
            if (in_array($contract->production_status, self::PRODUCTION_STAGES, true)) {
                return $contract->production_status;
            }

            return 'contrato';
        }
        if ($this->school_id || $this->status === 'cerrado') {
            return 'ganada';
        }

        return array_key_exists($this->status, self::JOURNEY) ? $this->status : 'nuevo';
    }

    /**
     * Pasos para pintar el recorrido: done (ya pasó), current (aquí va), y la siguiente etapa.
     *
     * @return array<int, array{key:string,label:string,icon:string,phase:string,done:bool,current:bool,n:int}>
     */
    public function journey(): array
    {
        $keys = array_keys(self::JOURNEY);
        $at = array_search($this->journeyStage(), $keys, true);
        $contract = $this->school_id ? $this->contract() : null;
        $out = [];
        foreach (self::JOURNEY as $key => [$label, $icon, $phase]) {
            $i = array_search($key, $keys, true);
            $done = $i < $at || ($key === 'entregado' && $at === $i);
            // "Contrato firmado" solo cuenta como hecho si de verdad está firmado o ya arrancó producción.
            if ($key === 'contrato' && $i === $at && $contract && $contract->isSigned()) {
                $done = true;
            }
            $out[] = ['key' => $key, 'label' => $label, 'icon' => $icon, 'phase' => $phase,
                'done' => $done, 'current' => $i === $at && ! $done, 'n' => $i + 1];
        }
        // Empresas: la venta termina en "Ganada" (no pasan a colegio ni contrato escolar).
        if ($this->isBusiness()) {
            $out = array_values(array_filter($out, fn ($st) => $st['phase'] === 'venta' || $st['key'] === 'ganada'));
            foreach ($out as $k => $st) {
                if ($st['key'] === 'ganada') {
                    $out[$k]['label'] = 'Ganada';
                    $out[$k]['done'] = $this->status === 'cerrado';
                    $out[$k]['current'] = false;
                }
            }
            if ($this->status !== 'cerrado') {
                $at = array_search($this->journeyStage(), array_column($out, 'key'), true);
                foreach ($out as $k => $st) {
                    $out[$k]['done'] = $k < $at;
                    $out[$k]['current'] = $k === $at;
                }
            }

            return $out;
        }
                // Contrato ya firmado: lo que sigue es la toma de medidas.
        if (! in_array(true, array_column($out, 'current'), true)) {
            foreach ($out as $k => $step) {
                if (! $step['done']) {
                    $out[$k]['current'] = true;
                    break;
                }
            }
        }

        return $out;
    }

    /** Contrato más reciente del colegio (si el cliente ya contrató). */
    public function contract(): ?Contract
    {
        return $this->school_id ? Contract::where('school_id', $this->school_id)->latest()->first() : null;
    }

    /**
     * Etapas que ve el cliente en su enlace: de la cotización a la entrega.
     *
     * @return array<int, array{label:string, done:bool, current:bool}>
     */
    public function customerSteps(): array
    {
        $steps = ['Cotización enviada', 'Revisando la propuesta', 'Contrato firmado', 'Toma de medidas', 'En producción', 'Entrega'];
        if ($this->isBusiness()) {
            $steps[3] = 'Diseño y tallas';
        }
        $contract = $this->isBusiness() ? null : $this->contract();

        if ($contract) {
            $map = ['cotizado' => 2, 'aprobado' => 2, 'toma_medidas' => 3, 'en_produccion' => 4, 'control_calidad' => 4, 'listo' => 5, 'entregado' => 6];
            $at = $map[$contract->production_status] ?? 2;
        } else {
            $at = match ($this->status) {
                'cerrado'            => 2,
                'vista', 'negociando'=> 1,
                default              => 0,
            };
        }

        $out = [];
        foreach ($steps as $i => $label) {
            $out[] = ['label' => $label, 'done' => $i < $at || ($i === 5 && $at === 6), 'current' => $i === min($at, 5) && $at < 6];
        }

        return $out;
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
        if ($this->isCustom()) {
            $lines = array_map(fn ($l) => ['label' => $l['qty'].' × '.$l['name'], 'price' => $l['subtotal']], $this->businessLines());

            return ['lines' => $lines, 'unit_total' => $this->businessTotals()['total']];
        }

        return QuoteCatalog::summarize($this->items);
    }

    /* ===================== Cotizaciones para empresas ===================== */

    public const BUSINESS_GIFT_EVERY = 25;

    /** Tipos de prenda sugeridos en el formulario de empresas. */
    public const BUSINESS_GARMENTS = ['Chaqueta', 'Chaqueta impermeable', 'Buso', 'Chaleco', 'Camiseta polo', 'Camiseta deportiva', 'Camisa', 'Gorra', 'Pantalón', 'Otro'];

    public function isBusiness(): bool
    {
        return $this->source === 'empresa';
    }

    public function scopeBusiness($query)
    {
        return $query->where('source', 'empresa');
    }

    /** Cotización personalizada para colegio (prendas con foto y valor por estudiante). */
    public function isSchoolCustom(): bool
    {
        return $this->source === 'personalizada';
    }

    /** ¿Se arma con prendas propias (empresa o colegio personalizada)? */
    public function isCustom(): bool
    {
        return $this->isBusiness() || $this->isSchoolCustom();
    }

    public function scopeKind($query, string $source)
    {
        return $query->where('source', $source);
    }

    /** Ruta para editar las prendas de una cotización personalizada / de empresa. */
    public function customEditUrl(): ?string
    {
        return match ($this->source) {
            'empresa'       => route('admin.business.edit', $this),
            'personalizada' => route('admin.custom.edit', $this),
            default         => null,
        };
    }

    /** Datos de la empresa (NIT, ciudad, cargo del contacto). */
    public function company(): array
    {
        return array_merge(['nit' => null, 'course' => null, 'city' => null, 'role' => null, 'validity' => 30], (array) ($this->config['company'] ?? []));
    }

    /**
     * Prendas de la cotización empresarial, listas para mostrar.
     *
     * @return array<int, array{garment:string,name:string,features:array,qty:int,unit_price:int,subtotal:int,photo:?string,photo_url:?string}>
     */
    public function businessLines(): array
    {
        $out = [];
        foreach ((array) ($this->items['lines'] ?? []) as $l) {
            $qty = max(0, (int) ($l['qty'] ?? 0));
            $unit = max(0, (int) round((float) ($l['unit_price'] ?? 0)));
            $photo = $l['photo'] ?? null;
            $out[] = [
                'garment'    => (string) ($l['garment'] ?? ''),
                'name'       => (string) ($l['name'] ?? ''),
                'features'   => array_values(array_filter(array_map('trim', (array) ($l['features'] ?? [])))),
                'qty'        => $qty,
                'unit_price' => $unit,
                'subtotal'   => $qty * $unit,
                'photo'      => $photo,
                'photo_url'  => $photo ? Storage::disk('public')->url($photo) : null,
            ];
        }

        return $out;
    }

    /** @return array{units:int,total:int,gift_jackets:int,missing_for_gift:int} */
    public function businessTotals(): array
    {
        $lines = $this->businessLines();
        $units = array_sum(array_column($lines, 'qty'));
        $every = self::BUSINESS_GIFT_EVERY;

        return [
            'units'            => $units,
            'total'            => array_sum(array_column($lines, 'subtotal')),
            'gift_jackets'     => intdiv($units, $every),
            'missing_for_gift' => $units % $every === 0 && $units > 0 ? 0 : $every - ($units % $every),
        ];
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

    public const DEFAULT_WA_MESSAGE_CUSTOM = "¡Hola {nombre}! 👋 Te comparto la cotización personalizada {numero} de G'Lauren para la promoción{colegio}.\n\n"
        ."Ahí ves cada prenda con su diseño, características, valor por estudiante y todo lo que incluye su prom:\n{enlace}\n\n"
        ."Te adjunto también el PDF. ¿Agendamos la toma de medidas? 🧥✨";

    public const DEFAULT_WA_MESSAGE_BUSINESS = "¡Hola {nombre}! 👋 Te comparto la cotización {numero} de G'Lauren para {empresa}.\n\n"
        ."Ahí encuentras cada prenda con sus características, el valor por unidad al por mayor y los obsequios incluidos:\n{enlace}\n\n"
        ."Te adjunto también el PDF. Quedo atento a cualquier ajuste. 🧥✨";

    public function whatsappMessage(?string $template = null): string
    {
        if ($this->isBusiness() && ! $template) {
            $template = Setting::get('quote_whatsapp_message_business') ?: self::DEFAULT_WA_MESSAGE_BUSINESS;
        }
        if ($this->isSchoolCustom() && ! $template) {
            $template = Setting::get('quote_whatsapp_message_custom') ?: self::DEFAULT_WA_MESSAGE_CUSTOM;
        }
        $template = $template ?: (Setting::get('quote_whatsapp_message') ?: self::DEFAULT_WA_MESSAGE);
        $first = (string) Str::of($this->customer_name)->explode(' ')->first();

        return strtr($template, [
            '{nombre}'   => $first,
            '{numero}'   => $this->code(),
            '{enlace}'   => $this->publicUrl(),
            '{colegio}'  => $this->school ? ' de '.$this->school : '',
            '{empresa}'  => $this->school ?: 'tu empresa',
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

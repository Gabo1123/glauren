<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = ['lead_id', 'name', 'phone', 'email', 'school', 'date', 'time', 'status', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** Franjas horarias del día (09:00 a 17:00, cada hora). */
    public const SLOTS = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    /** Devuelve las franjas libres para una fecha (excluye tomadas y horas pasadas si es hoy). */
    public static function availableSlots(string $date): array
    {
        $taken = static::where('date', $date)
            ->where('status', '!=', 'cancelada')
            ->pluck('time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->all();

        $isToday = $date === now()->toDateString();
        $nowHm   = now()->format('H:i');

        return array_values(array_filter(self::SLOTS, function ($slot) use ($taken, $isToday, $nowHm) {
            if (in_array($slot, $taken, true)) {
                return false;
            }
            if ($isToday && $slot <= $nowHm) {
                return false;
            }

            return true;
        }));
    }
}

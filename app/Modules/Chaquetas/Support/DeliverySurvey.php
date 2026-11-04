<?php

namespace App\Modules\Chaquetas\Support;

use App\Modules\Chaquetas\Mail\SimpleMail;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/** Encuesta y reseña al entregar: calificación, comentario, fotos y enlace a Google. */
class DeliverySurvey
{
    public static function url(Contract $contract): string
    {
        return URL::signedRoute('encuesta.show', ['contract' => $contract->id]);
    }

    public static function googleUrl(): ?string
    {
        $u = trim((string) Setting::get('google_review_url', ''));

        return $u !== '' ? $u : null;
    }

    /** Se le escribe al contacto del colegio (correo y WhatsApp). A los alumnos les llega en el aviso de "Entregado". */
    public static function sendToSchool(Contract $contract): array
    {
        $school = $contract->school;
        if (! $school) {
            return [];
        }
        $link = self::url($contract);
        $name = trim(explode(' ', (string) $school->contact_name)[0]) ?: 'equipo';
        $msg = "¡Hola {$name}! 🎉 Ya entregamos las chaquetas de {$school->name}. "
            ."¿Nos ayudas con 1 minuto? Califícanos y, si quieres, compártenos fotos de la entrega: {$link}"
            .(self::googleUrl() ? "\n\n⭐ Y si te gustó, una reseña en Google nos ayuda muchísimo: ".self::googleUrl() : '');
        $sent = [];

        $wa = app(WhatsAppService::class);
        if ($school->contact_phone && $wa->configured()) {
            if ($wa->sendText($school->contact_phone, $msg)['ok'] ?? false) {
                $sent[] = 'WhatsApp';
            }
        }
        if ($school->contact_email) {
            try {
                Mail::to($school->contact_email)->send(new SimpleMail(
                    '¿Cómo te fue con G\'Lauren? Cuéntanos en 1 minuto',
                    '¡Gracias, '.$name.'! 💛',
                    "Ya entregamos las chaquetas de {$school->name}. Tu opinión nos ayuda a mejorar y a que otros colegios nos conozcan.\n\nCalifícanos y, si quieres, súbenos fotos de la entrega.",
                    [],
                    'Calificar la experiencia',
                    $link,
                    'Entrega completada',
                ));
                $sent[] = 'correo';
            } catch (\Throwable $e) {
                Log::warning('Encuesta de entrega: correo falló', ['contract' => $contract->id, 'msg' => $e->getMessage()]);
            }
        }

        return $sent;
    }
}

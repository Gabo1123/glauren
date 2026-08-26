<?php

namespace App\Modules\Chaquetas\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envío de mensajes por la API de WhatsApp Business (Meta Cloud API).
 *
 * Configura en .env:
 *   WHATSAPP_TOKEN=...            (token permanente)
 *   WHATSAPP_PHONE_ID=...         (ID del número emisor)
 *   WHATSAPP_API_VERSION=v21.0
 *   WHATSAPP_TEMPLATE=...         (nombre de plantilla aprobada, opcional)
 *   WHATSAPP_TEMPLATE_LANG=es
 *
 * Nota Meta: fuera de la ventana de 24h desde el último mensaje del cliente,
 * solo se pueden enviar PLANTILLAS aprobadas (usa sendTemplate). El texto libre
 * (sendText) sirve dentro de esa ventana o para pruebas.
 */
class WhatsAppService
{
    public function configured(): bool
    {
        return ! empty(config('services.whatsapp.token')) && ! empty(config('services.whatsapp.phone_id'));
    }

    /** Normaliza a formato internacional de Colombia (57XXXXXXXXXX). */
    public function normalize(?string $phone): ?string
    {
        $p = preg_replace('/\D/', '', (string) $phone);
        if (! $p) {
            return null;
        }
        if (! str_starts_with($p, '57') && strlen($p) <= 10) {
            $p = '57'.$p;
        }

        return $p;
    }

    /** Envía texto libre (solo válido dentro de la ventana de 24h). */
    public function sendText(string $to, string $message): array
    {
        return $this->send($to, [
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $message],
        ]);
    }

    /** Envía una imagen por URL pública (con texto opcional). */
    public function sendImage(string $to, string $imageUrl, string $caption = ''): array
    {
        return $this->send($to, [
            'type'  => 'image',
            'image' => array_filter(['link' => $imageUrl, 'caption' => $caption]),
        ]);
    }

    /**
     * Envía una plantilla aprobada. $bodyParams son los {{1}}, {{2}}… del cuerpo.
     */
    public function sendTemplate(string $to, ?string $template = null, array $bodyParams = []): array
    {
        $template = $template ?: config('services.whatsapp.template');
        if (! $template) {
            return ['ok' => false, 'error' => 'No hay plantilla configurada (WHATSAPP_TEMPLATE).'];
        }

        $components = [];
        if ($bodyParams) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(fn ($v) => ['type' => 'text', 'text' => (string) $v], $bodyParams),
            ];
        }

        return $this->send($to, [
            'type'     => 'template',
            'template' => array_filter([
                'name'       => $template,
                'language'   => ['code' => config('services.whatsapp.lang', 'es')],
                'components' => $components ?: null,
            ]),
        ]);
    }

    private function send(string $to, array $payload): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => 'WhatsApp API no configurada.'];
        }

        $to = $this->normalize($to);
        if (! $to) {
            return ['ok' => false, 'error' => 'Teléfono inválido.'];
        }

        $version = config('services.whatsapp.api_version', 'v21.0');
        $phoneId = config('services.whatsapp.phone_id');
        $url     = "https://graph.facebook.com/{$version}/{$phoneId}/messages";

        try {
            $res = Http::withToken(config('services.whatsapp.token'))
                ->acceptJson()
                ->timeout(20)
                ->post($url, array_merge(['messaging_product' => 'whatsapp', 'to' => $to], $payload));

            if ($res->successful()) {
                return ['ok' => true, 'id' => $res->json('messages.0.id')];
            }

            $err = $res->json('error.message') ?: 'Error HTTP '.$res->status();
            Log::warning('WhatsApp API error', ['to' => $to, 'error' => $err]);

            return ['ok' => false, 'error' => $err];
        } catch (\Throwable $e) {
            Log::error('WhatsApp API exception', ['msg' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'No se pudo conectar con WhatsApp.'];
        }
    }
}

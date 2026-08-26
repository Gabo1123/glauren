<?php

namespace App\Modules\Chaquetas\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Respuestas con IA usando la API de Google Gemini.
 * Configura en .env:  GEMINI_API_KEY=...   (opcional: GEMINI_MODEL=gemini-2.0-flash)
 */
class GeminiService
{
    public function configured(): bool
    {
        return ! empty(config('services.gemini.key'));
    }

    /**
     * Devuelve la respuesta de Gemini, o null si no está configurada / falla.
     *
     * @param  array  $history  Turnos previos: [['role'=>'user|model','text'=>...], ...] (para memoria)
     */
    public function reply(string $userText, string $system, array $history = []): ?string
    {
        if (! $this->configured() || trim($userText) === '') {
            return null;
        }

        $model = config('services.gemini.model', 'gemini-3.6-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".config('services.gemini.key');

        // Arma la conversación: historial + mensaje actual
        $contents = [];
        foreach ($history as $h) {
            $role = ($h['role'] ?? 'user') === 'model' ? 'model' : 'user';
            $text = trim((string) ($h['text'] ?? ''));
            if ($text !== '') {
                $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
            }
        }
        // Evita duplicar si el último turno del historial ya es este mismo mensaje del usuario
        $last = end($contents);
        if (! ($last && $last['role'] === 'user' && ($last['parts'][0]['text'] ?? '') === $userText)) {
            $contents[] = ['role' => 'user', 'parts' => [['text' => $userText]]];
        }

        try {
            $res = Http::timeout(20)->acceptJson()->post($url, [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => $contents,
                'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 400],
                'safetySettings' => [],
            ]);

            if ($res->successful()) {
                $txt = $res->json('candidates.0.content.parts.0.text');

                return $txt ? trim($txt) : null;
            }

            Log::warning('Gemini API error', ['status' => $res->status(), 'body' => $res->json('error.message')]);
        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['msg' => $e->getMessage()]);
        }

        return null;
    }
}

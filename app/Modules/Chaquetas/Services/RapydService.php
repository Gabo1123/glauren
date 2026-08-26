<?php

namespace App\Modules\Chaquetas\Services;

use App\Modules\Chaquetas\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Cliente mínimo para Rapyd Collect (Hosted Checkout).
 * Docs: https://docs.rapyd.net/ — autenticación con firma HMAC-SHA256.
 */
class RapydService
{
    public function configured(): bool
    {
        return ! empty(config('services.rapyd.access_key'))
            && ! empty(config('services.rapyd.secret_key'));
    }

    /** Crea una página de pago alojada por Rapyd y devuelve ['id' => ..., 'redirect_url' => ...]. */
    public function createCheckout(Student $student, float $amount, string $completeUrl, string $cancelUrl): array
    {
        $body = [
            'amount'                 => $amount,
            'currency'               => config('services.rapyd.currency'),
            'country'                => config('services.rapyd.country'),
            'merchant_reference_id'  => 'STU-'.$student->id.'-'.time(),
            'complete_checkout_url'  => $completeUrl,
            'cancel_checkout_url'    => $cancelUrl,
            'metadata'               => ['student_id' => $student->id],
        ];

        $resp = $this->request('post', '/v1/checkout', $body);

        if (! $resp->successful()) {
            throw new \RuntimeException('Rapyd respondió '.$resp->status().': '.$resp->body());
        }

        $data = $resp->json('data', []);

        return [
            'id'           => $data['id'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
        ];
    }

    /** Firma y ejecuta una petición a la API de Rapyd. */
    public function request(string $method, string $path, ?array $body = null)
    {
        $method = strtolower($method);
        $salt = Str::random(12);
        $timestamp = (string) time();

        $bodyStr = $body === null ? '' : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($bodyStr === '{}' || $bodyStr === '[]') {
            $bodyStr = '';
        }

        $signature = $this->sign($method, $path, $salt, $timestamp, $bodyStr);

        $headers = [
            'Content-Type' => 'application/json',
            'access_key'   => config('services.rapyd.access_key'),
            'salt'         => $salt,
            'timestamp'    => $timestamp,
            'signature'    => $signature,
        ];

        $url = rtrim(config('services.rapyd.base_url'), '/').$path;

        return Http::withHeaders($headers)->timeout(20)->send(strtoupper($method), $url, [
            'body' => $bodyStr,
        ]);
    }

    /** signature = base64( hex( HMAC_SHA256( to_sign, secret ) ) ) */
    private function sign(string $method, string $path, string $salt, string $timestamp, string $body): string
    {
        $access = (string) config('services.rapyd.access_key');
        $secret = (string) config('services.rapyd.secret_key');

        $toSign = $method.$path.$salt.$timestamp.$access.$secret.$body;
        $hash = hash_hmac('sha256', $toSign, $secret); // hex

        return base64_encode($hash);
    }

    /** Verifica (best-effort) la firma de un webhook de Rapyd. */
    public function verifyWebhook(string $urlPath, string $salt, string $timestamp, string $signature, string $rawBody): bool
    {
        $access = (string) config('services.rapyd.access_key');
        $secret = (string) config('services.rapyd.secret_key');

        $toSign = $urlPath.$salt.$timestamp.$access.$secret.$rawBody;
        $expected = base64_encode(hash_hmac('sha256', $toSign, $secret));

        return hash_equals($expected, $signature);
    }
}

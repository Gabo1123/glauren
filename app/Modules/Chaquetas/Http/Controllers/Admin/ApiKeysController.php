<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ApiKeysController extends Controller
{
    /** Campos secretos (se muestran ocultos) y normales. */
    private const SECRET = ['GEMINI_API_KEY', 'WHATSAPP_TOKEN', 'WHATSAPP_VERIFY_TOKEN', 'MAIL_PASSWORD'];

    private const FIELDS = [
        'IA (Gemini)' => [
            'GEMINI_API_KEY' => ['Llave de Gemini', 'Se saca gratis en aistudio.google.com/apikey'],
            'GEMINI_MODEL'   => ['Modelo (opcional)', 'Por defecto: gemini-2.0-flash'],
        ],
        'WhatsApp Business (Meta)' => [
            'WHATSAPP_TOKEN'        => ['Token permanente', 'Del panel de Meta / Facebook Developers'],
            'WHATSAPP_PHONE_ID'    => ['ID del número (Phone ID)', 'El identificador del número emisor'],
            'WHATSAPP_VERIFY_TOKEN' => ['Token de verificación del webhook', 'Cualquier palabra secreta que tú inventes'],
            'WHATSAPP_TEMPLATE'     => ['Nombre de plantilla aprobada (opcional)', 'El nombre exacto de tu plantilla aprobada en Meta'],
            'WHATSAPP_TEMPLATE_LANG' => ['Idioma de la plantilla', 'Ej: es o es_CO'],
        ],
        'Correo (SMTP del hosting)' => [
            'MAIL_HOST'         => ['Servidor de correo', 'Ej: mail.chaquetasdeprom.com'],
            'MAIL_PORT'         => ['Puerto', '465 (SSL) o 587 (TLS)'],
            'MAIL_USERNAME'     => ['Correo (usuario)', 'Ej: glauren@chaquetasdeprom.com'],
            'MAIL_PASSWORD'     => ['Contraseña del correo', 'La clave de esa cuenta de correo'],
            'MAIL_FROM_ADDRESS' => ['Correo remitente', 'Normalmente el mismo correo'],
        ],
    ];

    public function edit()
    {
        $env = $this->readEnv();
        $groups = self::FIELDS;
        $secret = self::SECRET;
        // Estado: qué llaves ya están puestas (sin revelar el valor)
        $isSet = [];
        foreach ($groups as $fields) {
            foreach ($fields as $key => $meta) {
                $val = $env[$key] ?? '';
                $isSet[$key] = trim($val) !== '' && ! in_array(strtolower($val), ['null', 'false'], true);
            }
        }
        // Valores visibles solo para los NO secretos
        $visible = [];
        foreach ($groups as $fields) {
            foreach ($fields as $key => $meta) {
                $visible[$key] = in_array($key, $secret, true) ? '' : ($env[$key] ?? '');
            }
        }

        $twofa = [
            'enabled' => Setting::get('admin_2fa_enabled', '0') === '1',
            'phone'   => Setting::get('admin_2fa_phone', ''),
        ];

        return view('admin.cms.apikeys', compact('groups', 'secret', 'isSet', 'visible', 'twofa'));
    }

    public function update(Request $request)
    {
        $updates = [];
        foreach (self::FIELDS as $fields) {
            foreach ($fields as $key => $meta) {
                // Borrar explícito
                if ($request->boolean("clear_$key")) {
                    $updates[$key] = '';
                    continue;
                }
                $val = $request->input("keys.$key");
                // Vacío = no tocar (mantiene lo actual). Solo actualiza si escribieron algo.
                if ($val !== null && trim($val) !== '') {
                    $updates[$key] = trim($val);
                }
            }
        }

        // El cifrado del correo se ajusta al puerto (465 = SSL, 587 = TLS)
        if (isset($updates['MAIL_PORT'])) {
            $updates['MAIL_ENCRYPTION'] = ((string) $updates['MAIL_PORT'] === '465') ? 'ssl' : 'tls';
        }

        $this->writeEnv($updates);

        // Seguridad: 2FA por WhatsApp (se guarda en base de datos, no en .env)
        Setting::put('admin_2fa_enabled', $request->boolean('admin_2fa_enabled') ? '1' : '0');
        Setting::put('admin_2fa_phone', trim((string) $request->input('admin_2fa_phone', '')));

        // Aplica cambios sin reiniciar
        Artisan::call('config:clear');

        return back()->with('ok', 'Configuración guardada y aplicada. ✅');
    }

    /** Lee el .env como arreglo clave=>valor. */
    private function readEnv(): array
    {
        $path = base_path('.env');
        if (! File::exists($path)) {
            return [];
        }
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if ($line === '' || str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $v = trim($v);
            // quita comillas envolventes
            if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) {
                $v = substr($v, 1, -1);
            }
            $out[trim($k)] = $v;
        }

        return $out;
    }

    /** Actualiza (o agrega) claves en el .env conservando el resto. */
    private function writeEnv(array $updates): void
    {
        $path = base_path('.env');
        $content = File::exists($path) ? File::get($path) : '';

        foreach ($updates as $key => $value) {
            $escaped = $this->escapeValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $key.'='.$escaped, $content);
            } else {
                $content = rtrim($content, "\n")."\n".$key.'='.$escaped."\n";
            }
        }

        File::put($path, $content);
    }

    private function escapeValue(string $value): string
    {
        if ($value === '') {
            return '';
        }
        // Envuelve en comillas si tiene espacios o caracteres especiales
        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}

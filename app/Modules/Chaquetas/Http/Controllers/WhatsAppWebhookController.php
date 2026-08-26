<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\WaMessage;
use App\Modules\Chaquetas\Services\GeminiService;
use App\Modules\Chaquetas\Services\OrderLookupService;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private WhatsAppService $wa,
        private GeminiService $ai,
        private OrderLookupService $orders,
    ) {}

    /** Verificación del webhook (Meta hace un GET al configurarlo). */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /** Recibe mensajes entrantes y responde automáticamente. */
    public function handle(Request $request)
    {
        try {
            $value = $request->input('entry.0.changes.0.value', []);
            foreach (($value['messages'] ?? []) as $m) {
                $from = $m['from'] ?? null;
                if (! $from) {
                    continue;
                }
                $body = trim($m['text']['body'] ?? '');
                $text = mb_strtolower($body);
                $waId = $m['id'] ?? null;
                $name = $value['contacts'][0]['profile']['name'] ?? null;

                // Evita procesar dos veces el mismo mensaje (Meta puede reintentar el webhook)
                if ($waId && WaMessage::where('wa_id', $waId)->exists()) {
                    continue;
                }

                // Registra/actualiza el contacto como lead
                $lead = Lead::firstOrCreate(
                    ['phone' => $from, 'source' => 'whatsapp'],
                    ['name' => $name ?: 'WhatsApp', 'message' => $body, 'accepted_terms' => true]
                );

                // Historial ANTES de guardar el mensaje nuevo (memoria de la conversación)
                $history = WaMessage::historyFor($from, 12);

                // Guarda el mensaje entrante
                WaMessage::create([
                    'phone' => $from, 'lead_id' => $lead->id,
                    'direction' => 'in', 'body' => $body, 'wa_id' => $waId,
                ]);

                // Si un humano ya está atendiendo (IA pausada), no respondemos automáticamente
                if ($lead->ai_paused) {
                    continue;
                }

                // ¿Pide hablar con un asesor humano? → pausa la IA y avisa
                if ($this->wantsHuman($text)) {
                    $lead->update(['ai_paused' => true, 'human_requested_at' => now()]);
                    $msg = '¡Claro! 🙌 Un asesor de Glauren te va a atender por aquí en un momento. Cuéntanos mientras: ¿de qué colegio eres y cuántos son?';
                    WaMessage::create(['phone' => $from, 'lead_id' => $lead->id, 'direction' => 'out', 'body' => $msg]);
                    $this->wa->sendText($from, $msg);
                    continue;
                }

                // ¿El cliente mandó su documento? → datos reales del pedido para la IA
                $system   = $this->systemPrompt();
                $student  = null;
                if ($doc = $this->orders->extractDocument($body)) {
                    $student = $this->orders->findByDocument($doc);
                    if ($student) {
                        $system .= "\n\n".$this->orders->contextFor($student);
                        // Vincula el teléfono con el pedido (para avisos automáticos)
                        if (! $student->phone) {
                            $student->update(['phone' => $from]);
                        }
                        // Guarda el colegio en el lead si lo tenemos
                        if (! $lead->school && $student->contract?->school?->name) {
                            $lead->update(['school' => $student->contract->school->name]);
                        }
                    }
                }

                // Responde con IA (Gemini) usando el historial; si falla, palabras clave.
                $reply = $this->ai->reply($body, $system, $history) ?: $this->buildReply($text);

                // Guarda la respuesta y la envía
                WaMessage::create([
                    'phone' => $from, 'lead_id' => $lead->id,
                    'direction' => 'out', 'body' => $reply,
                ]);
                $this->wa->sendText($from, $reply);

                // Si pidió fotos y tenemos su pedido, se las enviamos
                if ($student && $this->wantsPhotos($text)) {
                    foreach ($this->orders->latestPhotos($student) as $p) {
                        $sent = $this->wa->sendImage($from, $p['url'], $p['caption']);
                        WaMessage::create([
                            'phone' => $from, 'lead_id' => $lead->id,
                            'direction' => 'out',
                            'body' => '📷 '.($p['caption'] ?: 'Foto de avance').($sent['ok'] ? '' : ' (no se pudo enviar)'),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp webhook error', ['msg' => $e->getMessage()]);
        }

        // Meta espera 200 siempre
        return response()->json(['ok' => true]);
    }

    /** ¿El cliente pide hablar con una persona/asesor humano? */
    private function wantsHuman(string $text): bool
    {
        foreach (['asesor', 'humano', 'persona real', 'una persona', 'hablar con alguien', 'hablar con un', 'agente', 'me llaman', 'me pueden llamar', 'llamada', 'atienda alguien', 'no me sirve', 'no entiendo nada'] as $w) {
            if (str_contains($text, $w)) {
                return true;
            }
        }

        return false;
    }

    /** ¿El cliente está pidiendo ver fotos de avance? */
    private function wantsPhotos(string $text): bool
    {
        foreach (['foto', 'fotos', 'imagen', 'imágen', 'imagenes', 'imágenes', 'avance', 'como va', 'cómo va', 'ver la chaqueta', 'ver el buso'] as $w) {
            if (str_contains($text, $w)) {
                return true;
            }
        }

        return false;
    }

    /** Instrucciones para la IA (Gemini). */
    private function systemPrompt(): string
    {
        $base  = rtrim(config('app.url', 'https://chaquetasdeprom.com'), '/');
        $brand = Setting::get('brand_name', 'Glauren');
        $wa    = Setting::get('contact_whatsapp', '573246750207');
        $extra = Setting::get('wa_ai_prompt', '');
        $know  = trim((string) Setting::get('wa_ai_knowledge', ''));

        // Base de conocimiento: info del negocio + Preguntas Frecuentes activas
        $faqs = \App\Modules\Chaquetas\Models\Faq::where('active', true)->orderBy('sort')->get()
            ->map(fn ($f) => '• '.$f->question.' → '.trim(strip_tags($f->answer)))
            ->implode("\n");
        $kb = '';
        if ($know !== '' || $faqs !== '') {
            $kb = "\n\nBASE DE CONOCIMIENTO (úsala como verdad para responder; no inventes fuera de esto):\n".trim($know."\n".$faqs);
        }

        return trim("Eres el ASESOR DE VENTAS de {$brand} por WhatsApp. {$brand} fabrica CHAQUETAS y BUSOS de promoción (grado once) personalizados, en toda Colombia, con más de 10 años de experiencia.
TU OBJETIVO ES VENDER y captar el contacto del cliente. Sé cálido, entusiasta y persuasivo, pero natural (nada de sonar a robot ni insistir de más).
En CADA respuesta: (1) resuelve la duda, y (2) da un paso hacia la venta.
Pide de forma amable los datos clave para cotizar/atender: nombre, colegio, ciudad, cuántos estudiantes y qué prenda.
Usa como gancho la promo: si el estudiante es el PRIMERO DE SU SALÓN en contactarnos y luego se hace la contratación con su colegio, SU prenda (chaqueta o buso) es GRATIS para él/ella.
Guía a la acción: diseñar en 3D ({$base}/personalizar) o dejar sus datos para que un asesor le arme la propuesta.
SEGUIMIENTO DE PEDIDO: si preguntan por el estado, el pago o las fotos de SU pedido, pídeles amablemente su NÚMERO DE DOCUMENTO para consultarlo. Si abajo aparecen 'DATOS REALES DEL PEDIDO', úsalos para responder con precisión y ofrécele las fotos de avance.
Cierra SIEMPRE con una pregunta o llamado a la acción para continuar la conversación.
Responde en español colombiano, cercano y breve (máx 3–4 frases), con 1–2 emojis.
REGLA CRÍTICA: NUNCA des precios, cifras ni rangos; si preguntan por precio, entusiásmalos y pide los datos para cotizar.
Ventajas para vender: telas de alta calidad (Lafayette, Patprimo), bordados premium, diseño 3D propio, seguimiento con fotos reales, garantía de 1 año, producción ~35 días hábiles.
Si no sabes algo o piden un humano, di que un asesor les escribe enseguida. No hables de temas ajenos a {$brand}.
".($extra ? "\nInstrucciones del negocio: {$extra}" : '').$kb);
    }

    /** Respuesta por palabras clave (respaldo si la IA no está configurada; nunca menciona precios). */
    private function buildReply(string $text): string
    {
        $wa   = preg_replace('/\D/', '', Setting::get('contact_whatsapp', '573246750207'));
        $base = rtrim(config('app.url', 'https://chaquetasdeprom.com'), '/');
        $has  = fn (array $words) => (bool) array_filter($words, fn ($w) => str_contains($text, $w));

        if ($has(['diseñ', 'disen', 'personaliz', '3d'])) {
            return "¡Genial! 🎨 Diseña tu chaqueta o buso en 3D aquí: {$base}/personalizar\nCuando termines nos llega tu diseño y te asesoramos. ¿De qué colegio eres?";
        }
        if ($has(['seguimiento', 'estado', 'mi chaqueta', 'pedido', 'entrega'])) {
            return "Puedes ver el estado de tu chaqueta con tu número de documento aquí: {$base}/mi-chaqueta 📦";
        }
        if ($has(['precio', 'costo', 'valor', 'cuanto', 'cuánto', 'cotiz'])) {
            return "¡Con gusto te cotizamos! 🙌 Cuéntanos:\n• Nombre del colegio\n• Cuántos estudiantes son\n• Tipo (chaqueta, buso, camiseta)\nY un asesor te arma la propuesta. También puedes diseñarla en 3D: {$base}/personalizar";
        }
        if ($has(['gratis', 'obsequio', 'regalo', 'promo'])) {
            return "🎁 ¡Sí! Si eres el PRIMERO de tu salón en escribirnos y luego contratamos con tu colegio, TU chaqueta o buso es GRATIS para ti. Cuéntanos de qué colegio eres y cuántos son, y te contamos cómo aplica.";
        }
        if ($has(['hola', 'buenas', 'buenos', 'info', 'información', 'informacion'])) {
            return $this->welcome($base);
        }

        return $this->welcome($base);
    }

    private function welcome(string $base): string
    {
        return Setting::get('wa_auto_reply')
            ?: "¡Hola! 👋 Somos Glauren, fabricamos chaquetas y busos de promoción en toda Colombia. ¿En qué te ayudamos?\n\n• Diseña la tuya en 3D: {$base}/personalizar\n• Sigue tu pedido: {$base}/mi-chaqueta\n• O cuéntanos tu colegio y cuántos son para cotizarte.\n\nUn asesor te responderá enseguida. 💛";
    }
}

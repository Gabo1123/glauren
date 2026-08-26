<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Http\Controllers\WhatsAppWebhookController;
use App\Modules\Chaquetas\Models\Faq;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\GeminiService;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;

class AiTrainingController extends Controller
{
    public function edit()
    {
        $prompt    = Setting::get('wa_ai_prompt', '');
        $knowledge = Setting::get('wa_ai_knowledge', '');
        $faqCount  = Faq::where('active', true)->count();
        $configured = ! empty(config('services.gemini.key'));
        $notifyStage = Setting::get('wa_notify_stage', '1') === '1';

        return view('admin.cms.ia', compact('prompt', 'knowledge', 'faqCount', 'configured', 'notifyStage'));
    }

    public function update(Request $request)
    {
        Setting::put('wa_ai_prompt', (string) $request->input('wa_ai_prompt', ''));

        $knowledge = (string) $request->input('wa_ai_knowledge', '');

        // Si suben un archivo .txt, se agrega a la base de conocimiento
        if ($request->hasFile('knowledge_file')) {
            $request->validate(['knowledge_file' => 'file|mimes:txt|max:2048']);
            $content = trim((string) file_get_contents($request->file('knowledge_file')->getRealPath()));
            if ($content !== '') {
                $knowledge = trim($knowledge."\n\n".$content);
            }
        }

        Setting::put('wa_ai_knowledge', $knowledge);
        Setting::put('wa_notify_stage', $request->boolean('wa_notify_stage') ? '1' : '0');

        return back()->with('ok', 'Información de la IA guardada. ¡Ya está entrenada!');
    }

    /** Prueba en vivo: envía un mensaje de ejemplo a la IA y muestra la respuesta. */
    public function test(Request $request, GeminiService $ai, WhatsAppService $wa)
    {
        $msg = trim((string) $request->input('message', ''));
        if ($msg === '') {
            return back()->with('ai_error', 'Escribe un mensaje de prueba.');
        }

        // Guarda cambios sin publicar aún: usamos los valores enviados para probar tal cual
        Setting::put('wa_ai_prompt', (string) $request->input('wa_ai_prompt', Setting::get('wa_ai_prompt', '')));
        Setting::put('wa_ai_knowledge', (string) $request->input('wa_ai_knowledge', Setting::get('wa_ai_knowledge', '')));

        $ctrl = new WhatsAppWebhookController($wa, $ai, new \App\Modules\Chaquetas\Services\OrderLookupService());
        $sp = (new \ReflectionMethod($ctrl, 'systemPrompt'));
        $sp->setAccessible(true);
        $system = $sp->invoke($ctrl);

        $reply = $ai->reply($msg, $system);
        if (! $reply) {
            $bm = new \ReflectionMethod($ctrl, 'buildReply');
            $bm->setAccessible(true);
            $reply = $bm->invoke($ctrl, mb_strtolower($msg))
                .(config('services.gemini.key') ? '' : "\n\n(⚠ La IA de Gemini no está configurada aún: esta es la respuesta de respaldo por palabras clave.)");
        }

        return back()
            ->with('ai_test_msg', $msg)
            ->with('ai_test_reply', $reply)
            ->withInput();
    }
}

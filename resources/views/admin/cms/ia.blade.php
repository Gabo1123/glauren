@extends('layouts.admin')
@section('title', 'Entrenar IA')

@section('content')
<h3 class="mb-1"><i class="bi bi-robot"></i> Entrenar la IA de WhatsApp</h3>
<div class="text-secondary small mb-3" style="max-width:820px">
    Tu IA <b>vende sola</b>: responde, resuelve dudas y empuja al cliente a cotizar o diseñar en 3D.
    Aquí le “subes” toda la información de tu negocio. No hace falta programar: escribe o pega tu info y guarda.
</div>

@if(session('ai_error'))
    <div class="g-badge g-badge--warn" style="margin-bottom:14px;padding:10px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-exclamation-triangle"></i> {{ session('ai_error') }}</div>
@endif

<div class="d-flex flex-wrap gap-2 mb-3" style="max-width:900px">
    <span class="badge {{ $configured ? 'bg-success' : 'bg-secondary' }}">
        <i class="bi bi-cpu"></i> Gemini {{ $configured ? 'conectada' : 'no configurada' }}
    </span>
    <span class="badge bg-info text-dark"><i class="bi bi-question-circle"></i> {{ $faqCount }} FAQ en su memoria</span>
    <a href="{{ route('admin.seo.index') }}" class="badge bg-dark text-decoration-none"><i class="bi bi-plus-circle"></i> Agregar preguntas frecuentes</a>
</div>

@unless($configured)
<div class="card p-3 mb-3" style="max-width:900px;border-left:3px solid #e9b83f">
    <div class="small">
        <b>Para activar la IA:</b> pega tu llave de Google Gemini en el archivo <code>.env</code> como
        <code>GEMINI_API_KEY=tu_llave</code> y reinicia el servidor. Consíguela gratis en
        <code>aistudio.google.com/apikey</code>. Mientras tanto, la IA responde con mensajes por palabras clave.
    </div>
</div>
@endunless

<div class="card p-4" style="max-width:900px">
    <form method="POST" action="{{ route('admin.ia.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Instrucciones / personalidad de ventas --}}
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label"><b>1. Cómo debe vender y hablar</b></label>
            <div class="small text-secondary mb-2">Reglas de tono y venta: horarios, ciudades, cómo tratar al cliente, qué ofrecer primero, etc.</div>
            <textarea name="wa_ai_prompt" class="form-control" rows="5"
                placeholder="Ej: Atendemos lun–sáb 8am–6pm. Envíos a toda Colombia. Trata al cliente de tú, con energía. Si preguntan por tallas, invítalos a la guía de tallas. Siempre ofrece diseñar en 3D y menciona la promo del primer colegio gratis.">{{ old('wa_ai_prompt', $prompt) }}</textarea>
        </div>

        {{-- Base de conocimiento --}}
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label"><b>2. Información de tu negocio (base de conocimiento)</b></label>
            <div class="small text-secondary mb-2">
                Pega TODO lo que la IA debe saber: telas, colores, tiempos, ciudades, formas de pago, garantía, cómo es el proceso, preguntas típicas y sus respuestas… La IA responderá basándose en esto.
                <b>Nunca pongas precios</b> (no se muestran al cliente).
            </div>
            <textarea name="wa_ai_knowledge" class="form-control" rows="12" style="font-size:13px"
                placeholder="Ej:
- Fabricamos chaquetas, busos y camisetas de promoción de grado once.
- Telas: Lafayette y Patprimo. Bordados a mano de alta calidad.
- Cubrimos toda Colombia. Ciudades principales: Bogotá, Medellín, Cali...
- Tiempo de producción: ~35 días hábiles después de aprobar el diseño.
- Garantía de 1 año por defectos de fábrica.
- Proceso: diseñas en 3D → tu curso aprueba → firmas contrato → fabricamos con fotos reales → recibes.
- Promo: el primer colegio en contratar recibe una prenda GRATIS.
- Formas de pago: Nequi, Bancolombia (y comprobante). Abonos: 0% al inicio, 50% al final.">{{ old('wa_ai_knowledge', $knowledge) }}</textarea>

            <div class="mt-3">
                <label class="form-label small mb-1"><i class="bi bi-upload"></i> O sube un archivo de texto (.txt) y se agrega a lo de arriba</label>
                <input type="file" name="knowledge_file" class="form-control form-control-sm" accept=".txt">
            </div>
        </div>

        {{-- Avisos automáticos --}}
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            <label class="form-label"><b>3. Avisos automáticos</b></label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="notifSw" name="wa_notify_stage" value="1" {{ $notifyStage ? 'checked' : '' }}>
                <label class="form-check-label" for="notifSw">
                    Avisar al cliente por WhatsApp cuando su pedido <b>cambie de etapa</b> (En producción, Listo, etc.)
                </label>
            </div>
            <div class="small text-secondary mt-1">Solo se avisa a clientes que ya escribieron por WhatsApp y dieron su documento (quedan vinculados a su pedido).</div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-warning fw-bold"><i class="bi bi-check2-circle"></i> Guardar y entrenar</button>
            <button formaction="{{ route('admin.ia.test') }}" formmethod="POST" name="__test" value="1" class="btn btn-outline-secondary" formnovalidate>
                <i class="bi bi-chat-dots"></i> Guardar y probar abajo
            </button>
        </div>
    </form>
</div>

{{-- Probador en vivo --}}
<div class="card p-4 mt-3" style="max-width:900px">
    <label class="form-label"><b><i class="bi bi-chat-left-dots"></i> Prueba a tu IA</b></label>
    <div class="small text-secondary mb-2">Escribe como si fueras un cliente y mira cómo responde (usa lo que tengas guardado).</div>
    <form method="POST" action="{{ route('admin.ia.test') }}" class="d-flex gap-2">
        @csrf
        <input type="text" name="message" class="form-control" value="{{ old('message') }}" placeholder="Ej: Hola, ¿ustedes hacen chaquetas para mi colegio?">
        <button class="btn btn-dark"><i class="bi bi-send"></i> Enviar</button>
    </form>

    @if(session('ai_test_reply'))
        <div class="mt-3">
            <div class="p-2 px-3 mb-2" style="background:var(--g-surface-2);border-radius:12px 12px 12px 2px;max-width:80%">
                <div class="small text-secondary">Cliente</div>{{ session('ai_test_msg') }}
            </div>
            <div class="p-2 px-3 ms-auto" style="background:#dcf8c6;color:#111;border-radius:12px 12px 2px 12px;max-width:80%">
                <div class="small" style="color:#4a7a2a">IA · Glauren</div>{!! nl2br(e(session('ai_test_reply'))) !!}
            </div>
        </div>
    @endif
</div>
@endsection

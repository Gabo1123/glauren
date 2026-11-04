<?php

/*
|--------------------------------------------------------------------------
| Guías paso a paso para el cliente
|--------------------------------------------------------------------------
| Se muestran en la cotización, al firmar el contrato y en los portales del
| colegio y del estudiante. Cada paso tiene:
|   title  → nombre del paso
|   text   → qué pasa en ese paso
|   how    → qué tiene que hacer el cliente (se despliega en el paso actual)
|   icon   → emoji
| Puedes editar los textos aquí sin tocar las vistas.
*/

$guias = [

    // Cotización de colegio (enlace /cotizacion/...)
    'cotizacion' => [
        'title' => 'Así funciona tu proceso',
        'intro' => 'Te acompañamos desde esta cotización hasta el día de la entrega. Mira en qué paso vas y qué sigue.',
        'steps' => [
            ['icon' => '📄', 'title' => 'Revisa tu cotización', 'text' => 'Aquí abajo están los diseños, precios, combos, beneficios y todo lo que incluye tu prom.',
                'how' => ['Mira las hojas con calma; puedes descargarlas en PDF.', 'Compártela con tu curso y con los padres de familia.', 'Escoge el tipo de chaqueta, buso o combo que les gusta.']],
            ['icon' => '💬', 'title' => 'Resolvemos dudas y ajustamos', 'text' => 'Definimos juntos el diseño, los colores, los bordados y cuántos estudiantes son.',
                'how' => ['Escríbenos por WhatsApp con tus preguntas.', 'Cuéntanos cuántos estudiantes van a pedir.', 'Si quieren algo especial (paño, forro con nombres, capota), lo cotizamos.']],
            ['icon' => '✍️', 'title' => 'Firma del contrato', 'text' => 'Te enviamos el contrato por WhatsApp para firmarlo desde el celular, sin imprimir nada.',
                'how' => ['Abre el enlace del contrato que te enviamos.', 'Lee el contrato (valor, pagos 50/50, entrega y garantía).', 'Escribe tu documento y dibuja tu firma con el dedo.', 'Se paga el 50% de anticipo para iniciar.']],
            ['icon' => '📏', 'title' => 'Toma de medidas', 'text' => 'Vamos al colegio a tomar medidas (Bogotá y Cundinamarca). En otras ciudades te enviamos la tabla y la guía de tallas.',
                'how' => ['Cada estudiante entra a su portal con su documento y crea su PIN.', 'Revisa que su nombre esté bien escrito: así sale bordado.', 'Si hay votación de diseño, cada uno vota desde su portal.']],
            ['icon' => '🧵', 'title' => 'Producción', 'text' => 'Fabricamos las prendas en 25 a 35 días hábiles. Verás fotos reales del avance en el portal.',
                'how' => ['Entra al portal para ver el avance con fotos.', 'Ponte al día con los pagos para no retrasar la entrega.']],
            ['icon' => '🎉', 'title' => 'Entrega', 'text' => 'Entregamos en el colegio con globos y fotografía. Se paga el 50% restante.',
                'how' => ['Cada estudiante recibe su prenda empacada con su nombre.', '¡Disfruten su prom y compartan las fotos en el muro del curso!']],
        ],
        'tip' => '¿Tienes dudas en cualquier paso? Escríbenos por WhatsApp y te respondemos.',
    ],

    // Cotización de empresa
    'cotizacion_empresa' => [
        'title' => 'Así trabajamos con tu empresa',
        'intro' => 'Del sí a la entrega en pocos pasos. Mira en qué vamos y qué sigue.',
        'steps' => [
            ['icon' => '📄', 'title' => 'Revisa la cotización', 'text' => 'Cada prenda con su foto, características y valor por unidad al por mayor.',
                'how' => ['Revísala con tu equipo; puedes descargarla en PDF.', 'Confirma cantidades por prenda.']],
            ['icon' => '💬', 'title' => 'Ajustes', 'text' => 'Afinamos colores, logo, bordados o estampados y cantidades.',
                'how' => ['Escríbenos por WhatsApp con los cambios.', 'Envíanos el logo en buena calidad (PDF, AI o PNG).']],
            ['icon' => '✍️', 'title' => 'Aprobación y anticipo', 'text' => 'Nos confirmas el pedido y se paga el 50% de anticipo.',
                'how' => ['Confírmanos por WhatsApp que aprueban la cotización.', 'Envía el comprobante del anticipo por WhatsApp.']],
            ['icon' => '📏', 'title' => 'Diseño y tallas', 'text' => 'Te enviamos el diseño con tu logo y la tabla de tallas.',
                'how' => ['Aprueba el diseño final.', 'Envíanos la lista de tallas por persona.']],
            ['icon' => '🧵', 'title' => 'Confección', 'text' => 'Corte, confección, bordado o estampado y control de calidad: 25 a 35 días hábiles.',
                'how' => ['Te mantenemos al tanto del avance.']],
            ['icon' => '📦', 'title' => 'Entrega', 'text' => 'Entregamos empacado por persona y se paga el saldo del 50%.',
                'how' => ['Revisa el pedido al recibirlo.']],
        ],
        'tip' => 'Recuerda: por cada 25 unidades recibes 1 chaqueta adicional de obsequio.',
    ],

    // Firma del contrato (enlace de firma)
    'firma' => [
        'title' => 'Cómo firmar tu contrato',
        'intro' => 'Es fácil y desde el celular. Toma 2 minutos.',
        'steps' => [
            ['icon' => '📲', 'title' => 'Verifica tu identidad', 'text' => 'Te enviamos un código de 6 números a tu WhatsApp.',
                'how' => ['Toca "Enviarme el código por WhatsApp".', 'Abre WhatsApp, copia el código y escríbelo aquí.', 'Si no llega en 1 minuto, toca "Reenviar código".']],
            ['icon' => '📖', 'title' => 'Lee el contrato', 'text' => 'Desliza dentro del recuadro para leerlo completo.',
                'how' => ['Revisa: prendas, valor por estudiante y cantidad.', 'Pagos: 50% para iniciar y 50% al finalizar.', 'Entrega: 25 a 35 días hábiles. Garantía todo el año escolar.']],
            ['icon' => '🪪', 'title' => 'Escribe tus datos', 'text' => 'Tu nombre completo y tu número de documento.',
                'how' => ['Escríbelos igual que en tu cédula.']],
            ['icon' => '✍️', 'title' => 'Dibuja tu firma', 'text' => 'Firma con el dedo (o el mouse) dentro del recuadro blanco.',
                'how' => ['Si no te quedó bien, toca "Limpiar firma" y repite.', 'En el celular, ponlo horizontal para tener más espacio.']],
            ['icon' => '✅', 'title' => 'Toca "Firmar contrato"', 'text' => 'Tu firma queda guardada con fecha y hora.',
                'how' => ['Verás la confirmación en pantalla.', 'Cuando todos los acudientes firmen, seguimos con la toma de medidas.']],
        ],
        'tip' => 'Tu firma queda registrada con fecha, hora y tu número de documento. Si tienes dudas sobre el contrato, escríbenos por WhatsApp antes de firmar.',
    ],

    // Portal del colegio: una guía por etapa de producción (claves de Contract::STAGES)
    'portal_colegio' => [
        'title' => 'Qué sigue en tu pedido',
        'intro' => 'Te guiamos en cada etapa para que la entrega salga a tiempo.',
        'steps' => [
            ['key' => 'cotizado', 'icon' => '📄', 'title' => 'Cotizado', 'text' => 'Tenemos tu cotización y estamos preparando el contrato.',
                'how' => ['Confírmanos el número de estudiantes.', 'Revisa el contrato cuando te llegue y fírmalo en línea.']],
            ['key' => 'aprobado', 'icon' => '✍️', 'title' => 'Contrato y diseño', 'text' => 'Firma el contrato y aprueba el diseño final.',
                'how' => ['Si ves "Tu contrato está listo para firmar", tócalo y fírmalo.', 'Aprueba el diseño desde esta página.', 'Agrega la lista de estudiantes o comparte el enlace de registro.']],
            ['key' => 'toma_medidas', 'icon' => '📏', 'title' => 'Toma de medidas', 'text' => 'Tomamos medidas en el colegio o con la guía de tallas.',
                'how' => ['Asegúrate de que todos los estudiantes estén en la lista.', 'Pídeles que entren a su portal y revisen sus datos.']],
            ['key' => 'en_produccion', 'icon' => '🧵', 'title' => 'En producción', 'text' => 'Estamos fabricando. Aquí verás fotos reales del avance.',
                'how' => ['Revisa los pagos: los pendientes pueden retrasar la entrega.', 'Escríbenos por el chat si necesitas algo.']],
            ['key' => 'control_calidad', 'icon' => '🔍', 'title' => 'Control de calidad', 'text' => 'Revisamos cada prenda: costuras, bordados y cremalleras.',
                'how' => ['Coordinemos la fecha y hora de la entrega por el chat.']],
            ['key' => 'listo', 'icon' => '📦', 'title' => 'Listo para entregar', 'text' => 'Todo está empacado con el nombre de cada estudiante.',
                'how' => ['Confirma la fecha de entrega.', 'Recuerda el pago del 50% restante.']],
            ['key' => 'entregado', 'icon' => '🎉', 'title' => 'Entregado', 'text' => '¡Felicitaciones a la promoción!',
                'how' => ['Descarga el certificado de entrega.', 'Compártenos fotos: nos encanta verlas.']],
        ],
        'tip' => 'Cualquier duda, usa el chat de esta página: te responde el equipo de G\'Lauren.',
    ],

    // Portal del estudiante
    'portal_estudiante' => [
        'title' => 'Tu chaqueta paso a paso',
        'intro' => 'Lo que tienes que hacer en cada etapa, sin enredos.',
        'steps' => [
            ['key' => 'cotizado', 'icon' => '👋', 'title' => 'Completa tu perfil', 'text' => 'Sube tu foto y revisa tus datos.',
                'how' => ['Sube tu foto de perfil.', 'Revisa que tu nombre esté bien escrito: así sale bordado.']],
            ['key' => 'aprobado', 'icon' => '🗳️', 'title' => 'Vota el diseño', 'text' => 'Si tu curso tiene opciones de diseño, vota por tu favorita.',
                'how' => ['Busca "Vota el diseño de tu curso" y toca tu opción.']],
            ['key' => 'toma_medidas', 'icon' => '📏', 'title' => 'Toma de medidas', 'text' => 'Te tomamos medidas en el colegio o con la guía de tallas.',
                'how' => ['Ese día lleva puesta ropa delgada.', 'Si no estabas, avísale al representante del curso.']],
            ['key' => 'en_produccion', 'icon' => '🧵', 'title' => 'En producción', 'text' => 'Tu chaqueta se está fabricando. Mira aquí el avance con fotos.',
                'how' => ['Ponte al día con tu pago si aún no lo has hecho.', 'Sube fotos y escribe en el muro de tu curso.']],
            ['key' => 'control_calidad', 'icon' => '🔍', 'title' => 'Control de calidad', 'text' => 'Revisamos tu chaqueta una por una.',
                'how' => ['Ya casi: pronto te avisamos la fecha de entrega.']],
            ['key' => 'listo', 'icon' => '📦', 'title' => 'Lista', 'text' => 'Tu chaqueta está empacada con tu nombre.',
                'how' => ['Revisa que tu pago esté completo.']],
            ['key' => 'entregado', 'icon' => '🎉', 'title' => '¡Entregada!', 'text' => 'Disfruta tu chaqueta.',
                'how' => ['Sube tus fotos con la chaqueta al muro.']],
        ],
        'tip' => '¿Olvidaste tu PIN? En la entrada toca "Olvidé mi PIN" y te llega un código.',
    ],
];

// Firma sin código de verificación (enlace único del contrato): los mismos pasos sin el primero.
$guias['firma_simple'] = $guias['firma'];
$guias['firma_simple']['steps'] = array_values(array_slice($guias['firma']['steps'], 1));
$guias['firma_simple']['tip'] = 'Tu firma queda registrada con fecha, hora y tu número de documento. Después de firmar puedes descargar o imprimir una copia.';

return $guias;

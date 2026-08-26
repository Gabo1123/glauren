<?php

return [

    // Número de WhatsApp que recibe las cotizaciones (con código de país, solo dígitos).
    // Ej: 573001234567  ·  se configura en el .env con CHAQUETAS_WHATSAPP
    'whatsapp' => env('CHAQUETAS_WHATSAPP', ''),

    // Moneda mostrada en los precios
    'currency' => env('CHAQUETAS_CURRENCY', 'COP'),

    // Tamaño máximo de modelo .glb que puede subir el admin (MB)
    'max_model_mb' => env('CHAQUETAS_MAX_MODEL_MB', 50),

];

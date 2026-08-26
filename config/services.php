<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'rapyd' => [
        'access_key' => env('RAPYD_ACCESS_KEY'),
        'secret_key' => env('RAPYD_SECRET_KEY'),
        'base_url'   => env('RAPYD_BASE_URL', 'https://sandboxapi.rapyd.net'),
        'country'    => env('RAPYD_COUNTRY', 'CO'),
        'currency'   => env('RAPYD_CURRENCY', 'COP'),
    ],

    // WhatsApp Business (Meta Cloud API). Pega las llaves en tu .env.
    'whatsapp' => [
        'token'       => env('WHATSAPP_TOKEN'),           // token permanente del sistema
        'phone_id'    => env('WHATSAPP_PHONE_ID'),         // ID del número emisor
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'template'    => env('WHATSAPP_TEMPLATE'),         // nombre de plantilla aprobada (opcional)
        'lang'        => env('WHATSAPP_TEMPLATE_LANG', 'es'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),    // token para verificar el webhook en Meta
    ],

    // Google Gemini (IA para responder en WhatsApp)
    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
    ],

];

<?php

return [
    'whatsapp_bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3001'),
    'whatsapp_bridge_secret' => env('WHATSAPP_BRIDGE_SECRET', ''),
    'message_rate_limit_seconds' => (int) env('MESSAGE_RATE_LIMIT_SECONDS', 2),
    'message_queue_retries' => (int) env('MESSAGE_QUEUE_RETRIES', 3),
    'api_key_prefix' => env('API_KEY_PREFIX', 'cp_live_'),

    'business' => [
        'legal_name' => env('CONNECTPULSE_LEGAL_NAME', 'ConnectPulse'),
        'support_email' => env('CONNECTPULSE_SUPPORT_EMAIL', 'support@connectpulse.cloud'),
        'support_phone' => env('CONNECTPULSE_SUPPORT_PHONE', '+91 90000 00000'),
        'address' => env('CONNECTPULSE_ADDRESS', 'India'),
        'gstin' => env('CONNECTPULSE_GSTIN', ''),
        'website' => env('APP_URL', 'https://connectpulse.cloud'),
    ],

    'pricing' => [
        ['credits' => 500, 'price' => 499, 'label' => 'Starter'],
        ['credits' => 2000, 'price' => 1799, 'label' => 'Growth', 'popular' => true],
        ['credits' => 5000, 'price' => 3999, 'label' => 'Business'],
        ['credits' => 10000, 'price' => 7499, 'label' => 'Enterprise'],
    ],

    'credit_price_inr' => (float) env('CONNECTPULSE_CREDIT_PRICE_INR', 1),
];

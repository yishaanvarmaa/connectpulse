<?php

return [
    'whatsapp_bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3001'),
    'whatsapp_bridge_secret' => env('WHATSAPP_BRIDGE_SECRET', ''),
    'message_rate_limit_seconds' => (int) env('MESSAGE_RATE_LIMIT_SECONDS', 2),
    'message_queue_retries' => (int) env('MESSAGE_QUEUE_RETRIES', 3),
    'api_key_prefix' => env('API_KEY_PREFIX', 'cp_live_'),

    'business' => [
        'legal_name' => env('CONNECTPULSE_LEGAL_NAME', 'Ishnex Solutions Private Limited'),
        'product_name' => env('CONNECTPULSE_PRODUCT_NAME', 'ConnectPulse'),
        'support_email' => env('CONNECTPULSE_SUPPORT_EMAIL', 'support@connectpulse.cloud'),
        'support_phone' => env('CONNECTPULSE_SUPPORT_PHONE', ''),
        'address' => env('CONNECTPULSE_ADDRESS', 'India'),
        'gstin' => env('CONNECTPULSE_GSTIN', ''),
        'website' => env('APP_URL', 'https://connectpulse.cloud'),
        'payment_gateway' => env('CONNECTPULSE_PAYMENT_GATEWAY', 'Cashfree'),
    ],

    'pricing' => [
        ['price' => 1000, 'credits' => 4000, 'label' => 'Starter'],
        ['price' => 2500, 'credits' => 10000, 'label' => 'Growth', 'popular' => true],
        ['price' => 5000, 'credits' => 22000, 'label' => 'Business'],
        ['price' => 10000, 'credits' => 50000, 'label' => 'Enterprise'],
    ],

    'credit_price_inr' => env('CONNECTPULSE_CREDIT_PRICE_INR'),
];

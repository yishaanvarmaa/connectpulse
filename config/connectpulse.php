<?php

return [
    'whatsapp_bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3001'),
    'whatsapp_bridge_secret' => env('WHATSAPP_BRIDGE_SECRET', ''),
    'message_rate_limit_seconds' => (int) env('MESSAGE_RATE_LIMIT_SECONDS', 2),
    'message_queue_retries' => (int) env('MESSAGE_QUEUE_RETRIES', 3),

    'campaign_delay_min_seconds' => (int) env('CAMPAIGN_DELAY_MIN_SECONDS', 10),
    'campaign_delay_max_seconds' => (int) env('CAMPAIGN_DELAY_MAX_SECONDS', 20),
    'campaign_delay_absolute_min' => (int) env('CAMPAIGN_DELAY_ABSOLUTE_MIN', 5),
    'campaign_delay_absolute_max' => (int) env('CAMPAIGN_DELAY_ABSOLUTE_MAX', 300),
    'campaign_max_retries' => (int) env('CAMPAIGN_MAX_RETRIES', 3),
    'api_key_prefix' => env('API_KEY_PREFIX', 'cp_live_'),

    'business' => [
        'legal_name' => env('CONNECTPULSE_LEGAL_NAME', 'ConnectPulse'),
        'product_name' => env('CONNECTPULSE_PRODUCT_NAME', 'ConnectPulse'),
        'support_email' => env('CONNECTPULSE_SUPPORT_EMAIL', 'support@connectpulse.cloud'),
        'support_phone' => env('CONNECTPULSE_SUPPORT_PHONE', ''),
        'address' => env('CONNECTPULSE_ADDRESS', 'India'),
        'gstin' => env('CONNECTPULSE_GSTIN', ''),
        'website' => env('APP_URL', 'https://connectpulse.cloud'),
        'payment_gateway' => env('CONNECTPULSE_PAYMENT_GATEWAY', 'Razorpay'),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'pricing' => [
        ['price' => 1000, 'credits' => 4000, 'label' => 'Starter'],
        ['price' => 2500, 'credits' => 10000, 'label' => 'Growth', 'popular' => true],
        ['price' => 5000, 'credits' => 22000, 'label' => 'Business'],
        ['price' => 10000, 'credits' => 50000, 'label' => 'Enterprise'],
    ],

    'credit_price_inr' => env('CONNECTPULSE_CREDIT_PRICE_INR'),
];

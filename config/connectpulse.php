<?php

return [
    'whatsapp_bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3001'),
    'whatsapp_bridge_secret' => env('WHATSAPP_BRIDGE_SECRET', ''),
    'message_rate_limit_seconds' => (int) env('MESSAGE_RATE_LIMIT_SECONDS', 2),
    'message_queue_retries' => (int) env('MESSAGE_QUEUE_RETRIES', 3),
    'api_key_prefix' => env('API_KEY_PREFIX', 'cp_live_'),
];

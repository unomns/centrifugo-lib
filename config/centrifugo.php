<?php

return [
    'api_url' => env('CENTRIFUGO_API_URL', 'http://localhost:8000/api'),
    'api_key' => env('CENTRIFUGO_API_KEY', ''),
    'secret'  => env('CENTRIFUGO_HMAC_SECRET_KEY', ''),

    /*
     * Proxy request signature validation.
     * Centrifugo signs proxy HTTP requests with X-Centrifugo-Hmac-Sha256.
     * Set verify_proxy_signature to false only in local dev when Centrifugo
     * is not configured to sign requests. proxy_hmac_key is separate from
     * secret — it authenticates HTTP calls, not JWTs.
     */
    'verify_proxy_signature' => (bool) env('CENTRIFUGO_VERIFY_PROXY_SIGNATURE', true),
    'proxy_hmac_key'         => env('CENTRIFUGO_PROXY_HMAC_KEY', ''),

    'token_ttl' => [
        'auth' => (int) env('CENTRIFUGO_AUTH_TOKEN_TTL', 86400),
        'anon' => (int) env('CENTRIFUGO_ANON_TOKEN_TTL', 7200),
    ],

    'personal_channel_prefix' => env('CENTRIFUGO_PERSONAL_PREFIX', 'user'),

    'rpc_rate_limit' => [
        'max'    => (int) env('CENTRIFUGO_RPC_RATE_LIMIT_MAX', 30),
        'window' => (int) env('CENTRIFUGO_RPC_RATE_LIMIT_WINDOW', 60),
    ],

    /*
     * Map Centrifugo channel namespace strings to handler class names.
     *
     * Handlers can also be registered programmatically:
     *   CentrifugoServiceProvider::registerHandler('chat', ChatHandler::class);
     *
     * Programmatic registrations override config on conflict.
     */
    'handlers' => [
        // 'system' => \App\Websocket\Handlers\SystemHandler::class,
        // 'chat'   => \App\Websocket\Handlers\ChatHandler::class,
    ],
];

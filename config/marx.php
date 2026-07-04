<?php

return [
    // Marx IPG endpoint. Safe default kept so the URL alone never breaks a build.
    'api_url' => env('MARX_API_URL', 'https://payment.v4.api.marx.lk/api/v4/ipg/orders'),

    // Merchant API key — MUST be set in .env (no default; never hardcode the secret).
    'merchant_key' => env('MARX_MERCHANT_KEY'),
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Heleket Cryptocurrency Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuration for the Heleket crypto payment gateway. All values are read
    | from the environment so that no secrets are committed to the repository.
    | See https://doc.heleket.com for the full API reference.
    |
    */

    'merchant_uuid' => env('HELEKET_MERCHANT_UUID', ''),
    'api_key' => env('HELEKET_API_KEY', ''),
    'base_url' => env('HELEKET_BASE_URL', 'https://api.heleket.com/v1'),

    'url_callback' => env('HELEKET_CALLBACK_URL', ''),
    'url_return' => env('HELEKET_RETURN_URL', ''),
    'url_success' => env('HELEKET_SUCCESS_URL', ''),

    // Invoice lifetime in seconds (Heleket allows 300 - 43200).
    'lifetime' => env('HELEKET_INVOICE_LIFETIME', 3600),

    // Currency the invoice amount is denominated in. Heleket converts to the
    // crypto chosen by the customer on their hosted payment page.
    'currency' => env('HELEKET_CURRENCY', 'USD'),
];

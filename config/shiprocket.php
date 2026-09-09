<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shiprocket API
    |--------------------------------------------------------------------------
    |
    | Bearer token from Shiprocket dashboard → Settings → API Tokens.
    | Keep it in your .env file. Never hard-code it or commit it.
    |
    */

    'base_url' => env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in/v1/external'),

    'token' => env('SHIPROCKET_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'mode' => env('SHIPROCKET_MODE', 'live'),

    // Channel id for this Shopify store (from Shiprocket) used for order tags if needed.
    'channel_id' => env('SHIPROCKET_CHANNEL_ID', ''),

    // Whether to push orders to Shiprocket automatically after payment.
    'auto_push' => env('SHIPROCKET_AUTO_PUSH', true),

    // Pickup location name configured in Shiprocket (leave empty to use the default).
    'pickup_location' => env('SHIPROCKET_PICKUP_LOCATION', 'Primary'),

];
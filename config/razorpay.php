<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Razorpay Mode
    |--------------------------------------------------------------------------
    |
    | 'live' for production, 'test' for sandbox.
    |
    */

    'mode' => env('RAZORPAY_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Razorpay Credentials
    |--------------------------------------------------------------------------
    |
    | Keep these in your .env file. Never hard-code them or commit them.
    |
    */

    'key_id' => env('RAZORPAY_KEY_ID', ''),

    'key_secret' => env('RAZORPAY_KEY_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'currency' => env('RAZORPAY_CURRENCY', 'INR'),

    'display_currency' => env('RAZORPAY_DISPLAY_CURRENCY', 'INR'),

];
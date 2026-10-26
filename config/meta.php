<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta Pixel & Conversions API
    |--------------------------------------------------------------------------
    |
    | META_PIXEL_ID       – your dataset/pixel id (Events Manager → Settings)
    | META_CAPI_ACCESS_TOKEN – long-lived token generated in Events Manager
    |                        → Settings → Conversions API → Generate access token
    |                        Leave empty to disable server-side events.
    | META_CAPI_VERSION   – Graph API version used for the events endpoint
    | META_CAPI_TEST_CODE – optional "Test Event Code" from the Events Manager
    |                        test-events tool (applies to every event sent).
    |
    */

    'pixel_id' => env('META_PIXEL_ID', '2028244431374307'),

    'access_token' => env('META_CAPI_ACCESS_TOKEN'),

    'api_version' => env('META_CAPI_VERSION', 'v21.0'),

    'test_event_code' => env('META_CAPI_TEST_CODE'),

];

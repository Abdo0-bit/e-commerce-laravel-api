<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cart Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the cart settings for your application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cart Expiration Time
    |--------------------------------------------------------------------------
    |
    | This value determines how long cart data should be stored in Redis
    | before it expires. The value is in seconds. Default is 7 days (604800 seconds).
    | Set to 0 to disable expiration.
    |
    */

    'ttl' => env('CART_TTL', 604800), // 7 days in seconds

    /*
    |--------------------------------------------------------------------------
    | Guest Cart Expiration Time
    |--------------------------------------------------------------------------
    |
    | This value determines how long guest cart data should be stored in Redis
    | before it expires. Guest carts typically have shorter expiration times.
    | The value is in seconds. Default is 24 hours (86400 seconds).
    |
    */

    'guest_ttl' => env('GUEST_CART_TTL', 86400), // 24 hours in seconds

];

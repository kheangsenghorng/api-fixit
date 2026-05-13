<?php


return [
    'base_url' => env('PAYWAY_BASE_URL', 'https://checkout-sandbox.payway.com.kh'),
    'merchant_id' => env('PAYWAY_MERCHANT_ID'),
    'public_key' => env('PAYWAY_PUBLIC_KEY'),

    'return_url' => env('PAYWAY_RETURN_URL'),
    'cancel_url' => env('PAYWAY_CANCEL_URL'),
    'success_url' => env('PAYWAY_SUCCESS_URL'),

    'ios_deeplink' => env('PAYWAY_IOS_DEEPLINK'),
    'android_deeplink' => env('PAYWAY_ANDROID_DEEPLINK'),
];
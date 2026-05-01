<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'aba' => [
        'merchant_id' => env('ABA_MERCHANT_ID'),
        'api_key' => env('ABA_API_KEY'),
        'purchase_url' => env('ABA_PURCHASE_URL'),
        'check_url' => env('ABA_CHECK_TRANSACTION_URL'),
    ],
    'telegram' => [
        'gateway_token' => env('TELEGRAM_GATEWAY_TOKEN'),
        'gateway_url' => env('TELEGRAM_GATEWAY_URL'),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],
   'bakong_gateway' => [
        'url' => env('GATEWAY_BAKONG_API_URL', 'https://gateway.servicemeite.io'),
        'api_key' => env('GATEWAY_BAKONG_API_KEY'),
        'token' => env('BAKONG_TOKEN'),
   ],
   'bakong' => [
    'admin_account_id' => env('BAKONG_ADMIN_ACCOUNT_ID'),
    'merchant_name' => env('BAKONG_MERCHANT_NAME'),
    ],

    'deeplink' => [
        'app_name' => env('DEEP_LINK_APP_NAME', 'Fixit'),
        'icon_url' => env('DEEP_LINK_ICON_URL'),
        'callback_url' => env('DEEP_LINK_CALLBACK_URL'),
        'web_callback_url' => env('DEEP_LINK_WEB_CALLBACK_URL'),
    ],
    




];

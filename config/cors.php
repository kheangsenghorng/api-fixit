<?php

return [

    'paths' => [
    'api/*',
    'sanctum/csrf-cookie',
    'broadcasting/auth', // 👈 add this
     ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5173',
        'https://dashboard-fixit.vercel.app',
        'https://www.servicefixit.me',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ← change false to true

];
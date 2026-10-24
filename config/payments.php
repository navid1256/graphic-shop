<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'zibal'),

    'providers' => [
        'zibal' => [
            'merchant' => env('ZIBAL_MERCHANT', 'zibal'),
        ],

        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'sandbox' => env('ZARINPAL_SANDBOX', true),
            'auto_verify' => env('ZARINPAL_AUTO_VERIFY', false),
        ],
    ],
];

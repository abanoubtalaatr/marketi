<?php

return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'mock_mode' => env('PAYPAL_MOCK_MODE', false),
];

<?php

return [
    'merchant_id' => env('PAYHERE_MERCHANT_ID'),
    'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),
    'app_secret' => env('PAYHERE_APP_SECRET'),
    'app_id' => env('PAYHERE_APP_ID'),
    'mode' => env('PAYHERE_MODE', 'sandbox'), // sandbox or live
];

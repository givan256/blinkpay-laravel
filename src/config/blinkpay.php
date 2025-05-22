<?php

return [
    'username' => env('BLINK_PAYMENTS_USERNAME'),
    'password' => env('BLINK_PAYMENTS_PASSWORD'),
    'merchant_id' => env('BLINK_PAYMENTS_MERCHANT_ID', ''),
    'merchant_password' => env('BLINK_PAYMENTS_MERCHANT_PASSWORD', ''),
    'api_url' => env('BLINK_PAYMENTS_API_URL'),
    'banking_api_url' => env('BLINK_PAYMENTS_BANKING_API_URL', ''),
    'default_exchange_rate' => env('BLINK_PAYMENTS_DEFAULT_EXCHANGE_RATE', 3700),
    'exchange_rate_key' => env('BLINK_PAYMENTS_EXCHANGE_RATE_KEY'),
    'convert_to_ugx' => env('BLINK_PAYMENTS_CONVERT_TO_UGX', false),
]; 
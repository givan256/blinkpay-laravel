<?php

return [
    'username' => env('BLINKPAY_USERNAME'),
    'password' => env('BLINKPAY_PASSWORD'),
    'api_url' => env('BLINKPAY_API_URL'),
    'dollar_rate' => env('BLINKPAY_DOLLAR_RATE'),
    'openexchangerates_key' => env('BLINKPAY_OPENEXCHANGE_KEY'),
    'convert_to_ugx' => env('BLINKPAY_CONVERT_TO_UGX', false),
]; 
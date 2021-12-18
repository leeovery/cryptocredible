<?php

return [

    'coinbase' => [
        'api_key'    => env('COINBASE_API_KEY'),
        'api_secret' => env('COINBASE_API_SECRET'),
    ],

    'coinbase_pro' => [
        'api_key'        => env('COINBASE_PRO_API_KEY'),
        'api_secret'     => env('COINBASE_PRO_API_SECRET'),
        'api_passphrase' => env('COINBASE_PRO_API_PASSPHRASE'),
    ],

    'binance' => [
        'api_key'    => env('BINANCE_API_KEY'),
        'api_secret' => env('BINANCE_API_SECRET'),
    ],

];

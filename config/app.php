<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => 'Cryptocredible',

    'fiat_currencies' => [
        'ALL', 'AFN', 'ARS', 'AWG', 'AUD', 'AZN', 'BSD', 'BBD', 'BDT', 'BYR', 'BZD', 'BMD', 'BOB', 'BAM', 'BWP', 'BGN',
        'BRL', 'BND', 'KHR', 'CAD', 'KYD', 'CLP', 'CNY', 'COP', 'CRC', 'HRK', 'CUP', 'CZK', 'DKK', 'DOP', 'XCD', 'EGP',
        'SVC', 'EEK', 'EUR', 'FKP', 'FJD', 'GHC', 'GIP', 'GTQ', 'GGP', 'GYD', 'HNL', 'HKD', 'HUF', 'ISK', 'INR', 'IDR',
        'IRR', 'IMP', 'ILS', 'JMD', 'JPY', 'JEP', 'KZT', 'KPW', 'KRW', 'KGS', 'LAK', 'LVL', 'LBP', 'LRD', 'LTL', 'MKD',
        'MYR', 'MUR', 'MXN', 'MNT', 'MZN', 'NAD', 'NPR', 'ANG', 'NZD', 'NIO', 'NGN', 'NOK', 'OMR', 'PKR', 'PAB', 'PYG',
        'PEN', 'PHP', 'PLN', 'QAR', 'RON', 'RUB', 'SHP', 'SAR', 'RSD', 'SCR', 'SGD', 'SBD', 'SOS', 'ZAR', 'LKR', 'SEK',
        'CHF', 'SRD', 'SYP', 'TWD', 'THB', 'TTD', 'TRY', 'TRL', 'TVD', 'UAH', 'GBP', 'USD', 'UYU', 'UZS', 'VEF', 'VND',
        'YER', 'ZWD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | This value determines the "version" your application is currently running
    | in. You may want to follow the "Semantic Versioning" - Given a version
    | number MAJOR.MINOR.PATCH when an update happens: https://semver.org.
    |
    */

    'version' => app('git.version'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. This can be overridden using
    | the global command line "--env" option when calling commands.
    |
    */

    'env' => 'development',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Services\Buzz\Providers\BuzzServiceProvider::class,
        App\Providers\CoinbaseServiceProvider::class,
        App\Providers\CoinbaseProServiceProvider::class,
        App\Providers\BinanceServiceProvider::class,
    ],

];

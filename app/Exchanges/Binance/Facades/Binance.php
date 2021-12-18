<?php

namespace App\Exchanges\Binance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Exchanges\Binance\Binance
 */
class Binance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'binance';
    }
}

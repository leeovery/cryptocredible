<?php

namespace App\Exchanges\CoinbasePro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Exchanges\CoinbasePro\CoinbasePro
 */
class CoinbasePro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'coinbase-pro';
    }
}

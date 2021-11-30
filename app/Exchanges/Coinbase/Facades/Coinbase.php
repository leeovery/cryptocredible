<?php

namespace App\Exchanges\Coinbase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Exchanges\Coinbase\Coinbase
 */
class Coinbase extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'coinbase';
    }
}

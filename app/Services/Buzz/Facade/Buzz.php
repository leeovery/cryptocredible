<?php

namespace App\Services\Buzz\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\Buzz\Buzz|\Illuminate\Console\Command;
 */
class Buzz extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'buzz';
    }
}

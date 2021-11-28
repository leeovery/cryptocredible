<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Managers\TransactionOutputManager
 */
class TransactionOutputManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Managers\TransactionOutputManager::class;
    }
}

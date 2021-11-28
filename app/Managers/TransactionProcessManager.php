<?php

namespace App\Managers;

use App\Exchanges\Coinbase\CoinbaseTransactionProcessDirector;

class TransactionProcessManager
{
    public static function coinbase(): CoinbaseTransactionProcessDirector
    {
        return new CoinbaseTransactionProcessDirector;
    }
}

<?php

namespace App\Managers;

use App\Exchanges\Coinbase\CoinbaseTransactionProcessDirector;
use App\Exchanges\CoinbasePro\CoinbaseProTransactionProcessDirector;

class TransactionProcessManager
{
    public static function coinbase(): CoinbaseTransactionProcessDirector
    {
        return new CoinbaseTransactionProcessDirector;
    }

    public static function coinbasePro(): CoinbaseProTransactionProcessDirector
    {
        return new CoinbaseProTransactionProcessDirector;
    }
}

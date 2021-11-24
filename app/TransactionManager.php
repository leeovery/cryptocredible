<?php

namespace App;

use App\Exchanges\Coinbase\CoinbaseTransactionDirector;
use JetBrains\PhpStorm\Pure;

class TransactionManager
{
    #[Pure]
    public static function coinbase(): CoinbaseTransactionDirector
    {
        return new CoinbaseTransactionDirector();
    }
}

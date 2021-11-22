<?php

namespace App\Exchanges\Coinbase;

use App\Contracts\TransactionTransformer;
use App\Transaction;

class CoinbaseTransactionTransformer implements TransactionTransformer
{
    public function transformer(array $transaction): Transaction
    {
        Transaction::builder()
            ->setType();
    }
}

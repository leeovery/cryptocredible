<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Transaction;

final class BuyTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        return $transaction;
    }
}

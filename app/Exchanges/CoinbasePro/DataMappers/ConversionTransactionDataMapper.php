<?php

namespace App\Exchanges\CoinbasePro\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\ValueObjects\Transaction;

final class ConversionTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        // TODO: Need data...
        return $transaction;
    }
}

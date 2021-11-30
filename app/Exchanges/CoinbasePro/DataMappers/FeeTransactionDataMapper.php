<?php

namespace App\Exchanges\CoinbasePro\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class FeeTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        return $transaction
            ->setId($transaction->getRaw('id'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Trade())
            ->setFee(new Amount(
                $transaction->getRaw('amount'),
                $transaction->getRaw('currency')
            ));
    }
}

<?php

namespace App\Exchanges\CoinbasePro\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class MatchTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction = $transaction
            ->setId($transaction->getRaw('id'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Trade());

        $amount = new Amount(
            $transaction->getRaw('amount'),
            $transaction->getRaw('currency')
        );

        if ($this->isSellSide($transaction)) {
            $transaction->setSellAmount($amount);
        } else {
            $transaction->setBuyAmount($amount);
        }

        return $transaction;
    }

    private function isSellSide(Transaction $transaction): bool
    {
        return is_negative($transaction->getRaw('amount'));
    }
}

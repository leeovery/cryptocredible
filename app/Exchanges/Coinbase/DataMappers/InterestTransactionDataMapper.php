<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class InterestTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setStatus($transaction->getRaw('status'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Income())
            ->setBuyAmount(new Amount(
                $transaction->getRaw('amount.amount'),
                $transaction->getRaw('amount.currency')
            ))
            ->setNotes($transaction->getRaw('details.header').' via '.$transaction->getRaw('from.name'));

        return $transaction;
    }
}

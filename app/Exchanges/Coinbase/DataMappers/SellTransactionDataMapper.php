<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class SellTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setStatus($transaction->getRaw('status'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Trade())
            ->setBuyAmount(new Amount(
                $transaction->getRaw('sell.total.amount'),
                $transaction->getRaw('sell.total.currency')
            ))
            ->setSellAmount(new Amount(
                $transaction->getRaw('sell.amount.amount'),
                $transaction->getRaw('sell.amount.currency')
            ))
            ->setFee(new Amount(
                $transaction->getRaw('sell.fee.amount'),
                $transaction->getRaw('sell.fee.currency')
            ))
            ->setNotes($transaction->getRaw('details.title').' '.$transaction->getRaw('details.subtitle'));

        return $transaction;
    }
}

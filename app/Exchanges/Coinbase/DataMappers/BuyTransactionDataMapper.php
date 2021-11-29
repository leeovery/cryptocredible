<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class BuyTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setStatus($transaction->getRaw('status'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Trade())
            ->setBuyAmount(new Amount(
                $transaction->getRaw('buy.amount.amount'),
                $transaction->getRaw('buy.amount.currency')
            ))
            ->setSellAmount(new Amount(
                $transaction->getRaw('buy.subtotal.amount'),
                $transaction->getRaw('buy.subtotal.currency')
            ))
            ->setFee(new Amount(
                $transaction->getRaw('buy.fee.amount'),
                $transaction->getRaw('buy.fee.currency')
            ))
            ->setNotes($transaction->getRaw('details.title').' '.$transaction->getRaw('details.subtitle'));

        return $transaction;
    }
}

<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class TradeTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction = $transaction
            ->setType(TransactionType::Trade())
            ->setFee(new Amount(
                $transaction->getRaw('trade.fee.amount'),
                $transaction->getRaw('trade.fee.currency')
            ))
            ->setNotes($transaction->getRaw('details.header').' '.$transaction->getRaw('details.subtitle'));

        $amount = new Amount(
            $transaction->getRaw('amount.amount'),
            $transaction->getRaw('amount.currency')
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
        return is_negative($transaction->getRaw('amount.amount'));
    }
}

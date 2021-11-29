<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class FiatDepositTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setStatus($transaction->getRaw('status'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Deposit())
            ->setBuyAmount(new Amount(
                $transaction->getRaw('amount.amount'),
                $transaction->getRaw('amount.currency')
            ))
            ->setFee(new Amount(
                $transaction->getRaw('fiat_deposit.fee.amount'),
                $transaction->getRaw('fiat_deposit.fee.currency')
            ))
            ->setNotes($transaction->getRaw('details.title').' '.$transaction->getRaw('details.subtitle'));

        return $transaction;
    }
}

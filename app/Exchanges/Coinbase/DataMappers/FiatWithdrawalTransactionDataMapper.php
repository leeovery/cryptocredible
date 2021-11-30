<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class FiatWithdrawalTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setStatus($transaction->getRaw('status'))
            ->setDate($transaction->getRaw('created_at'))
            ->setType(TransactionType::Withdrawal())
            ->setSellAmount(new Amount(
                $transaction->getRaw('amount.amount'),
                $transaction->getRaw('amount.currency')
            ))
            ->setFee(new Amount(
                $transaction->getRaw('fiat_withdrawal.fee.amount'),
                $transaction->getRaw('fiat_withdrawal.fee.currency')
            ))
            ->setNotes($transaction->getRaw('details.title').' '.$transaction->getRaw('details.subtitle'));

        return $transaction;
    }
}

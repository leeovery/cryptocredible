<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class FiatWithdrawalTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Withdrawal())
            ->setSellAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setFee(new Amount(
                $this->getRaw('fiat_withdrawal.fee.amount'),
                $this->getRaw('fiat_withdrawal.fee.currency')
            ))
            ->setNotes($this->getRaw('details.title').' '.$this->getRaw('details.subtitle'));

        return $transaction;
    }
}

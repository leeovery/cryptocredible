<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class ExchangeDepositTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Withdrawal())
            ->setAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setNotes('Withdrawal to Coinbase Pro');

        return $transaction;
    }
}

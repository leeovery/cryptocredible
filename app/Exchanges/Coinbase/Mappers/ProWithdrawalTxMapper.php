<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class ProWithdrawalTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Deposit())
            ->setBuyAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setNotes('Deposit from Coinbase Pro');

        return $transaction;
    }
}

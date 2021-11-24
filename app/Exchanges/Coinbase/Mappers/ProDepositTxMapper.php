<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class ProDepositTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Withdrawal())
            ->setSellAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setNotes($this->getRaw('details.title').' '.$this->getRaw('details.subtitle'));

        return $transaction;
    }
}

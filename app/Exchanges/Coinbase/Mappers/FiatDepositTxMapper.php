<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class FiatDepositTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Deposit())
            ->setBuyAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setFee(new Amount(
                $this->getRaw('fiat_deposit.fee.amount'),
                $this->getRaw('fiat_deposit.fee.currency')
            ))
            ->setNotes($this->getRaw('details.title').' '.$this->getRaw('details.subtitle'));

        return $transaction;
    }
}

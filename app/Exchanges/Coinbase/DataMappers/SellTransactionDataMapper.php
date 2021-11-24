<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class SellTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        // crypto to fiat trade

        $transaction
            ->setType(TransactionType::Trade())
            ->setBuyAmount(new Amount(
                $this->getRaw('sell.total.amount'),
                $this->getRaw('sell.total.currency')
            ))
            ->setSellAmount(new Amount(
                $this->getRaw('sell.amount.amount'),
                $this->getRaw('sell.amount.currency')
            ))
            ->setFee(new Amount(
                $this->getRaw('sell.fee.amount'),
                $this->getRaw('sell.fee.currency')
            ))
            ->setNotes($this->getRaw('details.title').' '.$this->getRaw('details.subtitle'));

        return $transaction;
    }
}


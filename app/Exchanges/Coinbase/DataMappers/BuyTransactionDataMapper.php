<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class BuyTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setType(TransactionType::Trade())
            ->setBuyAmount(new Amount(
                $this->getRaw('buy.amount.amount'),
                $this->getRaw('buy.amount.currency')
            ))
            ->setSellAmount(new Amount(
                $this->getRaw('buy.subtotal.amount'),
                $this->getRaw('buy.subtotal.currency')
            ))
            ->setFee(new Amount(
                $this->getRaw('buy.fee.amount'),
                $this->getRaw('buy.fee.currency')
            ))
            ->setNotes($this->getRaw('details.title').' '.$this->getRaw('details.subtitle'));

        return $transaction;
    }
}

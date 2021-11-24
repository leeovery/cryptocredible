<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class BuyTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        // BUY
        // * fiat to crypto trade
        //      - fee - buy.fee
        //      - purchased with asset amount (net of fee) = buy.subtotal
        //      - purchase asset amount = buy.amount
        //      - if using debit card (or non coinbase fiat wallet) then we need to create a
        //      - fiat deposit to match the fee + total (i.e. excluding payment method fee) with a note
        //      - in the note col stating the payment method fee and that this was a debit card purchase

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

<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class SendTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $type = $this->decideTransactionType();

        $transaction
            ->setTxHash($this->getRaw('network.hash'))
            ->setTxUrl($this->getRaw('network.transaction_url'))
            ->setNotes($this->getRaw('details.header').' '.$this->getRaw('details.subtitle'))
            ->setType($type);

        if ($type->is(TransactionType::Withdrawal())) {
            $transaction
                ->setSellAmount(new Amount(
                    $this->getRaw('network.transaction_amount.amount'),
                    $this->getRaw('network.transaction_amount.currency')
                ))
                ->setFee(new Amount(
                    $this->getRaw('network.transaction_fee.amount'),
                    $this->getRaw('network.transaction_fee.currency')
                ));
        } else {
            $transaction->setBuyAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ));
        }

        if ($type->is(TransactionType::Income())) {
            $transaction->setNotes($this->getRaw('details.header').' via '.$this->getRaw('from.name'));
        }

        return $transaction;
    }

    private function decideTransactionType(): TransactionType
    {
        $fromName = $this->getRaw('from.name');

        if ($fromName && str($fromName)->lower()->contains('coinbase earn')) {
            return TransactionType::Income();
        }

        $description = $this->getRaw('description');
        if ($description && str($description)->lower()->contains('earn task')) {
            return TransactionType::Income();
        }

        if (is_negative($this->getRaw('amount.amount'))) {
            return TransactionType::Withdrawal();
        }

        return TransactionType::Deposit();
    }
}


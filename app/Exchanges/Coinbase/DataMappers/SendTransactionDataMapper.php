<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class SendTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $type = $this->decideTransactionType($transaction);

        $transaction
            ->setTxHash($transaction->getRaw('network.hash'))
            ->setTxUrl($transaction->getRaw('network.transaction_url'))
            ->setNotes($transaction->getRaw('details.header').' '.$transaction->getRaw('details.subtitle'))
            ->setType($type);

        if ($type->is(TransactionType::Withdrawal())) {
            $transaction
                ->setSellAmount(new Amount(
                    $transaction->getRaw('network.transaction_amount.amount'),
                    $transaction->getRaw('network.transaction_amount.currency')
                ))
                ->setFee(new Amount(
                    $transaction->getRaw('network.transaction_fee.amount'),
                    $transaction->getRaw('network.transaction_fee.currency')
                ));
        } else {
            $transaction->setBuyAmount(new Amount(
                $transaction->getRaw('amount.amount'),
                $transaction->getRaw('amount.currency')
            ));
        }

        if ($type->is(TransactionType::Income())) {
            $transaction->setNotes($transaction->getRaw('details.header').' via '.$transaction->getRaw('from.name'));
        }

        return $transaction;
    }

    private function decideTransactionType(Transaction $transaction): TransactionType
    {
        $fromName = $transaction->getRaw('from.name');

        if ($fromName && str($fromName)->lower()->contains('coinbase earn')) {
            return TransactionType::Income();
        }

        $description = $transaction->getRaw('description');
        if ($description && str($description)->lower()->contains('earn task')) {
            return TransactionType::Income();
        }

        if (is_negative($transaction->getRaw('amount.amount'))) {
            return TransactionType::Withdrawal();
        }

        return TransactionType::Deposit();
    }
}

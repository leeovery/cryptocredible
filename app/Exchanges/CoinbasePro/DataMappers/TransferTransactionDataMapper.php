<?php

namespace App\Exchanges\CoinbasePro\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;

final class TransferTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction
            ->setId($transaction->getRaw('id'))
            ->setDate($transaction->getRaw('created_at'))
            ->setBuyAmount(new Amount(
                $transaction->getRaw('amount'),
                $transaction->getRaw('currency')
            ));

        if ($this->isWithdrawal($transaction)) {
            $transaction
                ->setType(TransactionType::Withdrawal())
                ->setNotes('Funds moved to Coinbase from Coinbase Pro');
        } else {
            $transaction
                ->setType(TransactionType::Deposit())
                ->setNotes('Funds moved from Coinbase to Coinbase Pro');
        }

        return $transaction;
    }

    private function isWithdrawal(Transaction $transaction): bool
    {
        return is_negative($transaction->getRaw('amount'));
    }
}

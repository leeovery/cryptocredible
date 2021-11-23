<?php

namespace App\Exchanges\Coinbase;

use App\Contracts\TransactionBuilder;
use App\Enums\TransactionType;
use App\Transaction;

class CoinbaseTransactionBuilder implements TransactionBuilder
{
    private array $rawData;

    public function build(array $rawTxData): Transaction
    {
        $this->rawData = $rawTxData;

        dump($rawTxData);

        $transaction = (new Transaction)
            ->setType($this->calculateTxType())
            ->setAmount()
            ->setFee();

        // date
        // tx hash
        // tx url
        // notes

        dd($transaction);

        return $transaction;
    }

    private function calculateTxType(): TransactionType
    {
        switch ($this->getRawData('type')) {
            case 'buy':
            case 'sell':
            case 'trade':
                // buy = fiat to crypto trade
                // sell = crypto to fiat trade
                // crypto to crypto trade
                return TransactionType::Trade();
            case 'send':
                $fromName = $this->getRawData('from.name');
                if ($fromName && str($fromName)->lower()->contains('coinbase earn')) {
                    return TransactionType::Income();
                }

                $description = $this->getRawData('description');
                if ($description && str($description)->lower()->contains('earn task')) {
                    return TransactionType::Income();
                }

                if (is_negative($this->getRawData('amount.amount'))) {
                    return TransactionType::Withdrawal();
                }

                return TransactionType::Deposit();
            case 'pro_withdrawal':
                // deposit from coinbase pro
                return TransactionType::Deposit();
            case 'exchange_deposit':
                // withdrawal to exchange (coinbase pro only?)
                return TransactionType::Withdrawal();
            case 'exchange_withdrawal':
                // deposit from exchange (coinbase pro only?)
                return TransactionType::Deposit();
            case 'fiat_deposit':
                // deposit
                return TransactionType::Deposit();
            case 'fiat_withdrawal':
                // withdrawal
                return TransactionType::Withdrawal();
        }

        abort(400, 'Unmatchable transaction type');
    }

    private function getRawData($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }
}

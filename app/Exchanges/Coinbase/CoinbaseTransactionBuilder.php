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
        $transaction = (new Transaction)
            ->setType($this->calculateTxType());


        return $transaction;
    }

    private function calculateTxType(): TransactionType
    {
        // transactions types
        // send
        // - positive amount.amount === deposit
        // - negative amount.amount === withdrawal

        // exchange_deposit
        // - neg. amount.amount = withdrawal to coinbase pro? or any exchange?

        switch ($this->getRawData('type')) {
            case 'buy':
                // fiat to crypto trade
                break;
            case 'sell':
                // crypto to fiat trade
                break;
            case 'trade':
                // crypto to crypto trade
                break;
            case 'send':
                // if from.name is "Coinbase Earn" then this is income from reward system.
                // or "description": "Earn Task",

                if (is_negative($this->getRawData('amount.amount'))) {
                    return TransactionType::Withdrawal();
                }

                return TransactionType::Deposit();
            case 'pro_withdrawal':
                // deposit from coinbase pro
                break;
            case 'exchange_deposit':
                // withdrawal to exchange (coinbase pro only?)
                break;
            case 'exchange_withdrawal':
                // deposit?
                break;
            case 'fiat_deposit':
                // deposit
                break;
            case 'fiat_withdrawal':
                // withdrawal
                break;
        }
    }

    private function getRawData($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }
}

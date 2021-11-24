<?php

namespace App\Exchanges\Coinbase;

use App\Contracts\TransactionBuilder;
use App\Enums\TransactionType;
use App\Exchanges\Coinbase\Mappers\BuyTxMapper;
use App\Exchanges\Coinbase\Mappers\ExchangeDepositTxMapper;
use App\Exchanges\Coinbase\Mappers\ExchangeWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\FiatDepositTxMapper;
use App\Exchanges\Coinbase\Mappers\FiatWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\ProWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\SellTxMapper;
use App\Exchanges\Coinbase\Mappers\SendTxMapper;
use App\Exchanges\Coinbase\Mappers\TradeTxMapper;
use App\Exchanges\Coinbase\Mappers\TxMapper;
use App\Transaction;
use Illuminate\Support\Carbon;

class CoinbaseTransactionBuilder implements TransactionBuilder
{
    private array $rawData;

    public function build(array $rawTxData): Transaction
    {
        $this->rawData = $rawTxData;

        // dd($rawTxData);

        // pending object has all the fluent setters for building out the tx.
        // Create method is finally called back here on building, and PTx builds
        // out the TX using its own data

        // or just put setters on TX itself. builder + mapper know how to get data from raw,
        // and will construct TX and return.
        $transaction = (new Transaction)
            ->setRawData($this->rawData)
            ->setId($this->getRaw('id'))
            ->setStatus($this->getRaw('status'))
            ->setTxDate(Carbon::make($this->getRaw('created_at')));

        $transaction = $this->getMapperForTxType()->execute($transaction);

        // THOUGHTS....
        // * do we need to check status??
        // * get payment methods to know when crypto has been purchased with a debit card, so we can
        //   create a fiat deposit to balance things.

        // NOTES ON TYPES & MAPPINGS
        // SEND
        // * if amount > 0 then it's a deposit from another place
        //      - use amount for amount
        // * if amount < 0 then it's a withdrawal, and we have to take account of fees
        //      - use network.transaction_fee as fee
        //      - use network.transaction_amount as amount

        // TRADE
        // * crypto to crypto trade
        //      - need to account for 2 sides of the trade
        //      - trades are split into 2 txs so use trade.id to get match the 2 txs
        //      - initially match tx type as TRADE with amount from amount.amount
        //      - buy side is positive, sell side is negative
        //      - both sides have a fee at trade.fee which in most cases seems to be zero but
        //      - include anyway and combine them when matching each side.

        // BUY
        // * fiat to crypto trade
        //      - fee - buy.fee
        //      - purchased with asset amount (net of fee) = buy.subtotal
        //      - purchase asset amount = buy.amount
        //      - if using debit card (or non coinbase fiat wallet) then we need to create a
        //      - fiat deposit to match the fee + total (i.e. excluding payment method fee) with a note
        //      - in the note col stating the payment method fee and that this was a debit card purchase

        // SELL
        // * crypto to fiat trade


        // * generic values
        // tx date - created_at
        // tx hash - network.hash - not always present
        // tx url - network.transaction_url - not always present
        // notes - details.header + details.subtitle

        return $transaction;
    }

    private function getMapperForTxType(): TxMapper
    {
        $lookup = [
            // 'buy'                 => BuyTxMapper::class,
            // 'sell'                => SellTxMapper::class,
            'trade'               => TradeTxMapper::class,
            'send'                => SendTxMapper::class,
            'pro_withdrawal'      => ProWithdrawalTxMapper::class,
            'exchange_deposit'    => ExchangeDepositTxMapper::class,
            // 'exchange_withdrawal' => ExchangeWithdrawalTxMapper::class,
            // 'fiat_deposit'        => FiatDepositTxMapper::class,
            // 'fiat_withdrawal'     => FiatWithdrawalTxMapper::class,
        ];

        $type = $this->getRaw('type');
        $mapper = $lookup[$type] ?? abort(400, "No matching tx mapper found for coinbase tx type '{$type}'.");

        return new $mapper($this->getRaw());
    }

    private function getRaw($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }

    private function calculateTxType(): TransactionType
    {
        switch ($this->getRaw('type')) {
            case 'buy':
            case 'sell':
            case 'trade':
                // buy = fiat to crypto trade
                // sell = crypto to fiat trade
                // crypto to crypto trade
                return TransactionType::Trade();
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
}

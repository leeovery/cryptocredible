<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Amount;
use App\Enums\TradeSide;
use App\Enums\TransactionType;
use App\PartialTradeTransaction;
use App\Transaction;

final class TradeTransactionDataMapper extends TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $transaction = PartialTradeTransaction::createFromTransaction($transaction)
            ->setType(TransactionType::Trade())
            ->setFee(new Amount(
                $this->getRaw('trade.fee.amount'),
                $this->getRaw('trade.fee.currency')
            ))
            ->setMatchableId($this->getRaw('trade.idem'))
            ->setNotes($this->getRaw('details.header').' '.$this->getRaw('details.subtitle'));

        $amount = new Amount($this->getRaw('amount.amount'), $this->getRaw('amount.currency'));

        if ($this->isSellSide()) {
            $transaction
                ->setTradeSide(TradeSide::Sell())
                ->setSellAmount($amount);
        } else {
            $transaction
                ->setTradeSide(TradeSide::Buy())
                ->setBuyAmount($amount);
        }

        return $transaction;
    }

    private function isSellSide(): bool
    {
        return is_negative($this->getRaw('amount.amount'));
    }
}

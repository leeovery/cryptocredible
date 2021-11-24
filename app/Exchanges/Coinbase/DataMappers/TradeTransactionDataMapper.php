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
        $transaction
            ->setType(TransactionType::Trade())
            ->setFee(new Amount(
                $this->getRaw('trade.fee.amount'),
                $this->getRaw('trade.fee.currency')
            ))
            ->setNotes($this->getRaw('details.header').' '.$this->getRaw('details.subtitle'));

        $tradedAmount = new Amount(
            $this->getRaw('amount.amount'),
            $this->getRaw('amount.currency')
        );
        $tradeSide = is_negative($this->getRaw('amount.amount'))
            ? TradeSide::Sell()
            : TradeSide::Buy();

        if ($tradeSide->is(TradeSide::Buy())) {
            $transaction->setBuyAmount($tradedAmount);
        } else {
            $transaction->setSellAmount($tradedAmount);
        }

        return PartialTradeTransaction::createFromTransaction($transaction)
            ->setTradeSide($tradeSide)
            ->setMatchableId($this->getRaw('trade.idem'));
    }
}

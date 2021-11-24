<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Amount;
use App\Enums\TransactionType;
use App\Transaction;

final class TradeTxMapper extends TxMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        // TRADE
        // * crypto to crypto trade
        //      - need to account for 2 sides of the trade
        //      - trades are split into 2 txs so use trade.id to get match the 2 txs
        //      - initially match tx type as TRADE with amount from amount.amount
        //      - buy side is positive, sell side is negative
        //      - both sides have a fee at trade.fee which in most cases seems to be zero but
        //      - include anyway and combine them when matching each side.

        // we have to record whether its a buy or sell side of the trade
        // they will be combined to a standard trade type in the next phase
        // need to track the trade ID so we can match them up

        $transaction
            ->setType(TransactionType::Trade())
            ->setTradeId() // use trade.id, trade.transaction.id, trade.idem ???
            ->setAmount(new Amount(
                $this->getRaw('amount.amount'),
                $this->getRaw('amount.currency')
            ))
            ->setNotes($this->getRaw('details.header').' '.$this->getRaw('details.subtitle'));

        dd($transaction);

        return $transaction;
    }
}

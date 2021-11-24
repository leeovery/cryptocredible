<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\PartialTradeTransaction;
use App\Transaction;
use Illuminate\Support\Collection;

class MatchPartialTransactionsProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        [$partialTransactions, $transactions] = $transactions->partition(function (Transaction $transaction) {
            return class_implements($transaction, PartialTradeTransaction::class);
        });

        dd($partialTransactions);


        dd('MatchPartialTransactionsProcessor');


        return $next($transactions);
    }
}

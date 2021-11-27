<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use Illuminate\Support\Collection;

class FindDebitCardTradesAndAddFakeFiatDeposit implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        // TODO
        // * get payment methods to know when crypto has been purchased with a debit card, so we can
        //   create a fiat deposit to balance things.

        dd($transactions);

        return $next($transactions);
    }
}

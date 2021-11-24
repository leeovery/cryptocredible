<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use Illuminate\Support\Collection;

class MatchPartialTransactionsProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions): Collection
    {
        dump('MatchPartialTransactionsProcessor');

        return $transactions;
    }
}

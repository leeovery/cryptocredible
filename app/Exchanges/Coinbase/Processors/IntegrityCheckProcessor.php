<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use Illuminate\Support\Collection;

class IntegrityCheckProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions): Collection
    {
        // TODO: Implement handle() method.
    }
}

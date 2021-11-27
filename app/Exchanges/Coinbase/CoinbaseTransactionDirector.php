<?php

namespace App\Exchanges\Coinbase;

use App\Contracts\TransactionDirector;
use App\Exchanges\Coinbase\Processors\CreateFiatDepositsForCardPurchasesProcessor;
use App\Exchanges\Coinbase\Processors\MapRawDataToTransactionProcessor;
use App\Exchanges\Coinbase\Processors\MatchPartialTransactionsProcessor;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use function resolve;

class CoinbaseTransactionDirector implements TransactionDirector
{
    private array $processors = [
        MapRawDataToTransactionProcessor::class,
        MatchPartialTransactionsProcessor::class,
        CreateFiatDepositsForCardPurchasesProcessor::class,
    ];

    public function process(Collection $transactions): Collection
    {
        return resolve(Pipeline::class)
            ->send($transactions)
            ->through($this->processors)
            ->thenReturn();
    }
}

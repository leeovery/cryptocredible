<?php

namespace App\Exchanges\CoinbasePro;

use App\Contracts\TransactionProcessDirector;
use App\Exchanges\CoinbasePro\Processors\MapRawDataToTransactionProcessor;
use App\Exchanges\CoinbasePro\Processors\MatchOneSideTradeTransactionProcessor;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use function resolve;

class CoinbaseProTransactionProcessDirector implements TransactionProcessDirector
{
    private array $processors = [
        MapRawDataToTransactionProcessor::class,
        MatchOneSideTradeTransactionProcessor::class,
    ];

    public function process(Collection $transactions): Collection
    {
        return resolve(Pipeline::class)
            ->send($transactions)
            ->through($this->processors)
            ->thenReturn();
    }
}

<?php

namespace App\Exchanges\CoinbasePro;

use App\Contracts\TransactionProcessDirector;
use App\Contracts\TransactionProcessDirector as TransactionProcessDirectorContract;
use App\Exchanges\AbstractTransactionProcessDirector;
use App\Exchanges\CoinbasePro\Processors\MapRawDataToTransactionProcessor;
use App\Exchanges\CoinbasePro\Processors\MatchOneSideTradeTransactionProcessor;

class CoinbaseProTransactionProcessDirector extends AbstractTransactionProcessDirector implements
    TransactionProcessDirectorContract
{
    protected array $processors = [
        MapRawDataToTransactionProcessor::class,
        MatchOneSideTradeTransactionProcessor::class,
    ];
}

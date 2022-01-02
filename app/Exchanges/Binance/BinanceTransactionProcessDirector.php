<?php

namespace App\Exchanges\Binance;

use App\Contracts\TransactionProcessDirector as TransactionProcessDirectorContract;
use App\Exchanges\AbstractTransactionProcessDirector;
use App\Exchanges\Coinbase\Processors\CreateFiatDepositsForCardPurchasesProcessor;
use App\Exchanges\Coinbase\Processors\MapRawDataToTransactionProcessor;
use App\Exchanges\Coinbase\Processors\MatchOneSideTradeTransactionProcessor;

class BinanceTransactionProcessDirector extends AbstractTransactionProcessDirector implements
    TransactionProcessDirectorContract
{
    protected array $processors = [
        MapRawDataToTransactionProcessor::class,
        MatchOneSideTradeTransactionProcessor::class,
        CreateFiatDepositsForCardPurchasesProcessor::class,
    ];
}

<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\Enums\TradeSide;
use App\Enums\TransactionType;
use App\Exceptions\MatchPartialTradesException;
use App\PartialTradeTransaction as Partial;
use App\Transaction;
use Illuminate\Support\Collection;

class MatchPartialTransactionsProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        /** @var Collection $partials */
        /** @var Collection $transactions */
        [$partials, $transactions] = $transactions->partition(fn($tx) => is_a($tx, Partial::class));

        $partialsTotalCount = $partials->count();
        throw_unless($partialsTotalCount % 2 === 0, MatchPartialTradesException::unevenPartialsFound());

        $partials = $partials->groupBy('matchableId')->map(function (Collection $partialTrades) {
            throw_unless(
                $buySide = $partialTrades->firstWhere('tradeSide', TradeSide::Buy()),
                MatchPartialTradesException::missingBuySide()
            );
            throw_unless(
                $sellSide = $partialTrades->firstWhere('tradeSide', TradeSide::Sell()),
                MatchPartialTradesException::missingSellSide()
            );

            $txDate = $buySide->txDate->isBefore($sellSide->txDate)
                ? $buySide->txDate : $sellSide->txDate;

            return (new Transaction)
                ->setType(TransactionType::Trade())
                ->setRawData([$buySide->rawData, $sellSide->rawData])
                ->setBuyAmount($buySide->buyAmount)
                ->setSellAmount($sellSide->sellAmount)
                ->setTxDate($txDate)
                ->setNotes("{$sellSide->notes} / {$buySide->notes}")
                ->setId($sellSide->matchableId);
        });

        $expectedFinalCount = $transactions->count() + ($partialsTotalCount / 2);
        $transactions = $transactions->union($partials)->values();

        throw_unless(
            $transactions->count() === $expectedFinalCount,
            MatchPartialTradesException::unevenPartialsFound()
        );

        return $next($transactions);
    }
}

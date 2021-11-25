<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\Enums\TradeSide;
use App\Enums\TransactionType;
use App\Exceptions\MatchPartialTradesException;
use App\PartialTradeTransaction as Partial;
use App\Transaction;
use Illuminate\Support\Collection;

/**
 *
 * use App\Exchanges\Coinbase\Processors\MatchPartialTransactionsProcessor;
 * use App\Amount;
 * use App\Enums\TradeSide;
 * use App\Enums\TransactionType;
 * use App\PartialTradeTransaction;
 * use App\Transaction;
 * use Illuminate\Support\Carbon;
 *
 * $processor = resolve(MatchPartialTransactionsProcessor::class);
 *
 * $processor->handle(
 * collect([
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 * new Transaction(),
 *
 * (new PartialTradeTransaction())
 * ->setId('7a392510-a2ea-510c-9743-dca2c0bd04e9')
 * ->setStatus('completed')
 * ->setTxDate(Carbon::make('2021-09-16T18:40:01Z'))
 * ->setType(TransactionType::Trade())
 * ->setFee(new Amount('0.00000000', 'ETH'))
 * ->setBuyAmount(new Amount('0.00080412', 'ETH'))
 * ->setMatchableId('a596154d-94df-473a-b537-21bd7d468485')
 * ->setTradeSide(TradeSide::Buy()),
 *
 * (new \App\PartialTradeTransaction())
 * ->setId('d9baa033-7492-5f94-9dbb-910888534a6f')
 * ->setStatus('completed')
 * ->setTxDate(Carbon::make('2021-09-16T18:40:02Z'))
 * ->setType(TransactionType::Trade())
 * ->setFee(new Amount('0.00000000', 'ETH'))
 * ->setSellAmount(new Amount('0.11347883', 'BOND'))
 * ->setMatchableId('a596154d-94df-473a-b537-21bd7d468485')
 * ->setTradeSide(TradeSide::Sell()),
 * ]),
 * function () {}
 * );
 */
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

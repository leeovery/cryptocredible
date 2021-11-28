<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\Enums\TransactionType;
use App\Exceptions\MatchOneSideTradesException;
use App\ValueObjects\Transaction;
use Illuminate\Support\Collection;

class MatchOneSideTradeTransactionProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        /** @var Collection $partials */
        /** @var Collection $transactions */
        [$partials, $transactions] = $transactions->partition(function (Transaction $transaction) {
            if ($transaction->type->isNot(TransactionType::Trade())) {
                return false;
            }
            if (! is_null($transaction->buyAmount) && ! is_null($transaction->sellAmount)) {
                return false;
            }

            return true;
        });

        $partialsTotalCount = $partials->count();
        throw_unless($partialsTotalCount % 2 === 0, MatchOneSideTradesException::unevenPartialsFound());

        $partials = $partials
            ->groupBy(fn(Transaction $transaction) => $transaction->getRaw('trade.idem'))
            ->map(function (Collection $partialTrades) {
                throw_unless(
                    $buySide = $partialTrades->firstWhere('sellAmount', null),
                    MatchOneSideTradesException::missingBuySide()
                );
                throw_unless(
                    $sellSide = $partialTrades->firstWhere('buyAmount', null),
                    MatchOneSideTradesException::missingSellSide()
                );

                $txDate = $buySide->txDate->isBefore($sellSide->txDate)
                    ? $buySide->txDate : $sellSide->txDate;

                return (new Transaction($buySide->rawData))
                    ->setType(TransactionType::Trade())
                    ->setRawData([$buySide->rawData, $sellSide->rawData])
                    ->setBuyAmount($buySide->buyAmount)
                    ->setSellAmount($sellSide->sellAmount)
                    ->setTxDate($txDate)
                    ->setNotes("{$sellSide->notes} / {$buySide->notes}")
                    ->setId($sellSide->getRaw('trade.idem'));
            });

        $expectedFinalCount = $transactions->count() + ($partialsTotalCount / 2);
        $transactions = $transactions->push(...$partials)->values();

        throw_unless(
            $transactions->count() === $expectedFinalCount,
            MatchOneSideTradesException::unevenPartialsFound()
        );

        return $next($transactions);
    }
}

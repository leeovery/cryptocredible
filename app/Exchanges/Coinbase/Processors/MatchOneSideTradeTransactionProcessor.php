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
            ->groupBy(fn (Transaction $transaction) => $transaction->getRaw('trade.idem'))
            ->map(function (Collection $partialTrades) {
                /* @var Transaction $buySide */
                throw_unless(
                    $buySide = $partialTrades->firstWhere('sellAmount', null),
                    MatchOneSideTradesException::missingBuySide()
                );
                /* @var Transaction $sellSide */
                throw_unless(
                    $sellSide = $partialTrades->firstWhere('buyAmount', null),
                    MatchOneSideTradesException::missingSellSide()
                );

                $txDate = $buySide->date->isBefore($sellSide->date)
                    ? $buySide->date : $sellSide->date;

                return (new Transaction([$buySide->getRaw(), $sellSide->getRaw()]))
                    ->setStatus($buySide->getRaw('status'))
                    ->setType(TransactionType::Trade())
                    ->setBuyAmount($buySide->buyAmount)
                    ->setSellAmount($sellSide->sellAmount)
                    ->setDate($txDate)
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

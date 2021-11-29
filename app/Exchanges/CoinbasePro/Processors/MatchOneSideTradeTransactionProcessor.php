<?php

namespace App\Exchanges\CoinbasePro\Processors;

use App\Contracts\TransactionProcessor;
use App\Enums\TransactionType;
use App\Exceptions\MatchOneSideTradesException;
use App\ValueObjects\Transaction;
use Illuminate\Support\Collection;

class MatchOneSideTradeTransactionProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        // Match trades
        // Each trade should have at least 2 sides, and sometimes will also have a fee.

        /** @var Collection $partials */
        /** @var Collection $transactions */
        [$partials, $transactions] = $transactions->partition(function (Transaction $transaction) {
            if ($transaction->type->isNot(TransactionType::Trade())) {
                return false;
            }

            return true;
        });

        $partials = $partials
            ->groupBy(fn (Transaction $transaction) => $transaction->getRaw('details.trade_id'))
            ->map(function (Collection $partialTrades) {
                dd($partialTrades);
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

        $transactions = $transactions->push(...$partials)->values();

        return $next($transactions);
    }
}

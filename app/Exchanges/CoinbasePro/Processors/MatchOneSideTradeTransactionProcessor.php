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
        /** @var Collection $partials */
        /** @var Collection $transactions */
        [$partials, $transactions] = $transactions->partition(function (Transaction $transaction) {
            return $transaction->type->is(TransactionType::Trade());
        });

        $partials = $partials
            ->groupBy(fn (Transaction $transaction) => $transaction->getRaw('details.trade_id'))
            ->map(function (Collection $partialTrades, string $matchableId) {

                /* @var Transaction $buySide */
                throw_unless(
                    $buySide = $partialTrades->first(function (Transaction $transaction) {
                        return is_null($transaction->sellAmount) && is_null($transaction->fee);
                    }),
                    MatchOneSideTradesException::missingBuySide()
                );

                /* @var Transaction $sellSide */
                throw_unless(
                    $sellSide = $partialTrades->first(function (Transaction $transaction) {
                        return is_null($transaction->buyAmount) && is_null($transaction->fee);
                    }),
                    MatchOneSideTradesException::missingSellSide()
                );

                /* @var Transaction $feeSide */
                $feeSide = $partialTrades->first(function (Transaction $transaction) {
                    return ! is_null($transaction->fee)
                        && is_null($transaction->buyAmount)
                        && is_null($transaction->sellAmount);
                });

                return (new Transaction([$buySide->getRaw(), $sellSide->getRaw(), $feeSide?->getRaw()]))
                    ->setType(TransactionType::Trade())
                    ->setBuyAmount($buySide->buyAmount)
                    ->setSellAmount($sellSide->sellAmount)
                    ->setFee($feeSide?->fee)
                    ->setDate($buySide->date)
                    ->setId($matchableId);
            });

        $transactions = $transactions->push(...$partials)->values();

        return $next($transactions);
    }
}

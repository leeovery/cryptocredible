<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\Enums\TransactionType;
use App\ValueObjects\Amount;
use App\ValueObjects\Transaction;
use Brick\Money\Money;
use Illuminate\Support\Collection;

class CreateFiatDepositsForCardPurchasesProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        return $next(
            $transactions
                ->filter(fn (Transaction $transaction) => $transaction->type->is(TransactionType::Trade()))
                ->filter(function (Transaction $transaction) {
                    return str($transaction->getRaw('details.subtitle', ''))->contains('******');
                })
                ->map(function (Transaction $transaction) {
                    $subtotal = Money::of(
                        $transaction->getRaw('buy.subtotal.amount'),
                        $transaction->getRaw('buy.subtotal.currency')
                    );
                    $fee = Money::of(
                        $transaction->getRaw('buy.fee.amount'),
                        $transaction->getRaw('buy.subtotal.currency')
                    );

                    return (new Transaction($transaction->getRaw()))
                        ->setId($transaction->getRaw('id'))
                        ->setStatus($transaction->getRaw('status'))
                        ->setType(TransactionType::Deposit())
                        ->setBuyAmount(new Amount(
                            $subtotal->plus($fee)->getAmount(),
                            $transaction->getRaw('buy.subtotal.currency'),
                        ))
                        ->setDate($transaction->date->avoidMutation()->subSecond())
                        ->setNotes("Auto-created deposit linked to tx#{$transaction->id}. This was a debit/credit card purchase and this auto tx keeps your fiat accounts balanced.");
                })
                ->push(...$transactions)
                ->values()
        );
    }
}

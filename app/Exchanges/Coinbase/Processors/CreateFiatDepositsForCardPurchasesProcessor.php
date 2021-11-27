<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Amount;
use App\Contracts\TransactionProcessor;
use App\Enums\TransactionType;
use App\Transaction;
use Brick\Money\Money;
use Illuminate\Support\Collection;

class CreateFiatDepositsForCardPurchasesProcessor implements TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection
    {
        return $next(
            $transactions
                ->filter(fn(Transaction $transaction) => $transaction->type->is(TransactionType::Trade()))
                ->filter(function (Transaction $transaction) {
                    return str(data_get($transaction->rawData, 'details.subtitle', ''))->contains('******');
                })
                ->map(function (Transaction $transaction) {
                    $subtotal = Money::of(
                        data_get($transaction->rawData, 'buy.subtotal.amount'),
                        data_get($transaction->rawData, 'buy.subtotal.currency')
                    );
                    $fee = Money::of(
                        data_get($transaction->rawData, 'buy.fee.amount'),
                        data_get($transaction->rawData, 'buy.subtotal.currency')
                    );

                    return (new Transaction)
                        ->setType(TransactionType::Deposit())
                        ->setBuyAmount(new Amount(
                            $subtotal->plus($fee)->getAmount(),
                            data_get($transaction->rawData, 'buy.subtotal.currency'),
                        ))
                        ->setTxDate($transaction->txDate->subSecond())
                        ->setRawData($transaction->rawData)
                        ->setStatus($transaction->status)
                        ->setId($transaction->id)
                        ->setNotes("Auto-created deposit linked to tx#{$transaction->id}. This was a debit/credit card purchase and this auto tx keeps your fiat accounts balanced.");
                })
                ->push(...$transactions)
                ->values()
        );
    }
}

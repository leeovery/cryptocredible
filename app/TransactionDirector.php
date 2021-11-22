<?php

namespace App;

use App\Contracts\TransactionTransformer;
use App\Exchanges\Coinbase\CoinbaseTransactionTransformer;
use Illuminate\Support\Collection;

class TransactionDirector
{
    private TransactionTransformer $transformer;

    public static function coinbase(): self
    {
        return (new self)->withTransformer(new CoinbaseTransactionTransformer);
    }

    public function withTransformer(TransactionTransformer $transformer): static
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function transform(Collection $transactions): Collection
    {
        return $transactions->map(fn(array $transaction) => $this->transformer->transformer($transaction));
    }
}

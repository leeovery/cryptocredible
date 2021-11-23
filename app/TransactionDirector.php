<?php

namespace App;

use App\Contracts\TransactionBuilder;
use App\Exchanges\Coinbase\CoinbaseTransactionBuilder;
use Illuminate\Support\Collection;

class TransactionDirector
{
    private TransactionBuilder $builder;

    public static function coinbase(): self
    {
        return (new self)->setBuilder(new CoinbaseTransactionBuilder);
    }

    public function setBuilder(TransactionBuilder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }

    public function mapCollection(Collection $transactions): Collection
    {
        return $transactions->map(fn(array $transaction) => $this->builder->build($transaction));
    }
}

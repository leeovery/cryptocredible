<?php

namespace App\Exchanges\Coinbase\DataMappers;

use App\Transaction;

abstract class TransactionDataMapper
{
    public function __construct(protected array $rawData) {}

    abstract public function execute(Transaction $transaction): Transaction;

    protected function getRaw($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }
}

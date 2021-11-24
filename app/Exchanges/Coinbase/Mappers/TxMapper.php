<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Transaction;

abstract class TxMapper
{
    public function __construct(protected array $rawData) {
        // dump($this->rawData);
    }

    abstract public function execute(Transaction $transaction): Transaction;

    protected function getRaw($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }
}

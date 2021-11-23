<?php

namespace App\Exchanges\Coinbase\Mappers;

use App\Transaction;

abstract class TxMapper
{
    public function __construct(protected array $rawData, protected Transaction $transaction) { }

    abstract public function execute(): Transaction;
}

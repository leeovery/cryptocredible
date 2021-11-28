<?php

namespace App\Contracts;

use App\ValueObjects\Transaction;

interface TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction;
}

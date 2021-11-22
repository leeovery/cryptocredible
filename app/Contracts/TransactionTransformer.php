<?php

namespace App\Contracts;

use App\Transaction;

interface TransactionTransformer
{
    public function transformer(array $transaction): Transaction;
}

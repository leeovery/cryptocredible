<?php

namespace App\Contracts;

use App\Transaction;

interface TransactionBuilder
{
    public function build(array $transaction): Transaction;
}

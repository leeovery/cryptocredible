<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface TransactionProcessor
{
    public function handle(Collection $transactions, callable $next): Collection;
}

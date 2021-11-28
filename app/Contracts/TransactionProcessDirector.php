<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface TransactionProcessDirector
{
    public function process(Collection $transactions): Collection;
}

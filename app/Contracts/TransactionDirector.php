<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface TransactionDirector
{
    public function process(Collection $transactions): Collection;
}

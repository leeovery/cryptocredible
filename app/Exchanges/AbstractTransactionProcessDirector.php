<?php

namespace App\Exchanges;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

abstract class AbstractTransactionProcessDirector
{
    public function process(Collection $transactions): Collection
    {
        return resolve(Pipeline::class)
            ->send($transactions)
            ->through($this->processors)
            ->thenReturn();
    }
}

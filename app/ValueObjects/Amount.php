<?php

namespace App\ValueObjects;

use Brick\Math\BigNumber;

class Amount
{
    public BigNumber $value;

    public function __construct(string|BigNumber $value, public string $currency)
    {
        $this->value = BigNumber::of($value)->abs();
    }
}

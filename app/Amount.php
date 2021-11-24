<?php

namespace App;

class Amount
{
    public int|float $value;

    public function __construct(int|float|string $value, public string $currency)
    {
        $this->value = abs(filter_var($value, FILTER_VALIDATE_INT | FILTER_VALIDATE_FLOAT));
    }
}

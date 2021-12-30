<?php

namespace App\Exchanges\Binance\Exceptions;

use Exception;

class BinanceException extends Exception
{
    public static function requestFailed($message = 'Fetching binance resource failed'): self
    {
        return new self($message, 400);
    }
}

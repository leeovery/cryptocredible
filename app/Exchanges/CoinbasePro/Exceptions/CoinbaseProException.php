<?php

namespace App\Exchanges\CoinbasePro\Exceptions;

use Exception;

class CoinbaseProException extends Exception
{
    public static function requestFailed($message = 'Fetching coinbase pro resource failed'): self
    {
        return new self($message, 400);
    }
}

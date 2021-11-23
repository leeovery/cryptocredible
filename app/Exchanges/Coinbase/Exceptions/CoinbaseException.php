<?php

namespace App\Exchanges\Coinbase\Exceptions;

use Exception;

class CoinbaseException extends Exception
{
    public static function requestFailed($message = 'Fetching coinbase resource failed'): CoinbaseException
    {
        return new self($message, 400);
    }
}


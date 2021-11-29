<?php

namespace App\Exceptions;

use Exception;

class MatchOneSideTradesException extends Exception
{
    public static function unevenPartialsFound(): self
    {
        return new self('Uneven number of partials found', 400);
    }

    public static function missingBuySide(): self
    {
        return new self('Missing buy side', 400);
    }

    public static function missingSellSide(): self
    {
        return new self('Missing sell side', 400);
    }
}

<?php

namespace App\Exceptions;

use Exception;

class MatchPartialTradesException extends Exception
{
    public static function unevenPartialsFound(): MatchPartialTradesException
    {
        return new self('Uneven number of partials found', 400);
    }

    public static function missingBuySide(): MatchPartialTradesException
    {
        return new self('Missing buy side', 400);
    }

    public static function missingSellSide(): MatchPartialTradesException
    {
        return new self('Missing sell side', 400);
    }
}


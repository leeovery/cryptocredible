<?php

namespace App\Exceptions;

use Exception;

class MatchOneSideTradesException extends Exception
{
    public static function unevenPartialsFound(): MatchOneSideTradesException
    {
        return new self('Uneven number of partials found', 400);
    }

    public static function missingBuySide(): MatchOneSideTradesException
    {
        return new self('Missing buy side', 400);
    }

    public static function missingSellSide(): MatchOneSideTradesException
    {
        return new self('Missing sell side', 400);
    }
}


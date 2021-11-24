<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Buy()
 * @method static static Sell()
 */
final class TradeSide extends Enum
{
    const Buy  = 0;
    const Sell = 1;
}

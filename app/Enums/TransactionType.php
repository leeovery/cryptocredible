<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Transfer()
 * @method static static Deposit()
 * @method static static Withdrawal()
 */
final class TransactionType extends Enum
{
    const Transfer   = 0;
    const Deposit    = 1;
    const Withdrawal = 2;
}

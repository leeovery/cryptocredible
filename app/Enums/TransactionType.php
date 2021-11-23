<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Transfer()
 * @method static static Deposit()
 * @method static static Withdrawal()
 * @method static static Trade()
 * @method static static Income()
 */
final class TransactionType extends Enum
{
    const Transfer   = 0;
    const Deposit    = 1;
    const Withdrawal = 2;
    const Trade = 3;
    const Income = 4;
}

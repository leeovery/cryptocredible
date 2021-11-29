<?php

/** @noinspection ALL */

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Deposit()
 * @method static static Withdrawal()
 * @method static static Trade()
 * @method static static Income()
 */
final class TransactionType extends Enum
{
    public const Deposit = 0;

    public const Withdrawal = 1;

    public const Trade = 2;

    public const Income = 3;
}

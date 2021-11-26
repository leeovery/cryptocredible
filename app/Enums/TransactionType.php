<?php /** @noinspection ALL */

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
    const Deposit    = 0;
    const Withdrawal = 1;
    const Trade      = 2;
    const Income     = 3;
}

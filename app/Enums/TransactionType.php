<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionType extends Enum
{
    const TRANSFER   = 0;
    const DEPOSIT    = 1;
    const WITHDRAWAL = 2;
}

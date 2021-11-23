<?php

namespace App;

use App\Enums\TransactionType;

class Transaction
{
    public TransactionType $type;

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }
}

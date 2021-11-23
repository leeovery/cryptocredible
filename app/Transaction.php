<?php

namespace App;

use App\Enums\TransactionType;

class Transaction
{
    public TransactionType $type;

    protected $amount;

    protected $fee;

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function setFee($fee): static
    {
        $this->fee = $fee;

        return $this;
    }
}

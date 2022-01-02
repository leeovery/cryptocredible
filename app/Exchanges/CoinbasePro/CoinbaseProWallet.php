<?php

namespace App\Exchanges\CoinbasePro;

class CoinbaseProWallet
{
    protected string $id;

    private string $currency;

    public function __construct(array $rawData)
    {
        $this->id = $rawData['id'];
        $this->currency = $rawData['currency'];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}

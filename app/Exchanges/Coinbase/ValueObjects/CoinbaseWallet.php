<?php

namespace App\Exchanges\Coinbase\ValueObjects;

class CoinbaseWallet
{
    protected string $id;

    protected string $name;

    protected string $resourcePath;

    public function __construct(array $rawData)
    {
        $this->id = $rawData['id'];
        $this->name = $rawData['name'];
        $this->resourcePath = $rawData['resource_path'];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function resourcePath(): string
    {
        return $this->resourcePath;
    }
}

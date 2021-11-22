<?php

namespace App\Exchanges\Coinbase;

class CoinbaseAccount
{
    protected string $id;

    protected string $name;

    protected string $type;

    protected string $resourcePath;

    protected array $rawData;

    public function __construct(array $rawData)
    {
        $this->rawData = $rawData;
        $this->id = $rawData['id'];
        $this->name = $rawData['name'];
        $this->type = $rawData['type'];
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

    public function type(): string
    {
        return $this->type;
    }

    public function resourcePath(): string
    {
        return $this->resourcePath;
    }

    public function rawData(): array
    {
        return $this->rawData;
    }
}

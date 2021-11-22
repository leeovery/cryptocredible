<?php

namespace App\Exchanges\Coinbase;

use Illuminate\Support\Carbon;

class CoinbaseTransaction
{
    protected string $id;

    protected string $type;

    protected string $resourcePath;

    protected array $rawData;

    protected string $status;

    protected string $amountValue;

    protected string $amountCurrency;

    protected string $nativeValue;
    protected string $nativeCurrency;

    protected null|string $description;

    protected Carbon $createdAt;
    protected Carbon $updatedAt;

    public function __construct(array $rawData)
    {
        $this->rawData = $rawData;
        $this->id = $rawData['id'];
        $this->type = $rawData['type'];
        $this->status = $rawData['status'];
        $this->amountValue = data_get($rawData, 'amount.amount');
        $this->amountCurrency = data_get($rawData, 'amount.currency');
        $this->nativeValue = data_get($rawData, 'native_amount.amount');
        $this->nativeCurrency = data_get($rawData, 'native_amount.currency');
        $this->description = $rawData['description'];

        $this->createdAt = Carbon::create($rawData['created_at']);
        $this->updatedAt = Carbon::create($rawData['updated_at']);


        $this->details = $rawData['details'];
        $this->to = $rawData['to'];
        $this->from = $rawData['from'];
        $this->address = $rawData['address'];
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

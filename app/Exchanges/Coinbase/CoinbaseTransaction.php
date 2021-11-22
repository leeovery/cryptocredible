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

    protected null|string $transactionUrl;

    protected null|string $transactionHash;

    protected null|string $transactionFeeAmount;

    protected null|string $transactionFeeCurrency;

    protected null|string $transactionAmount;

    protected null|string $transactionCurrency;

    protected string $details;

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
        $this->resourcePath = $rawData['resource_path'];
        $this->transactionUrl = data_get($rawData, 'network.transaction_url');
        $this->transactionHash = data_get($rawData, 'network.hash');
        $this->transactionFeeAmount = data_get($rawData, 'network.transaction_fee.amount');
        $this->transactionFeeCurrency = data_get($rawData, 'network.transaction_fee.currency');
        $this->transactionAmount = data_get($rawData, 'network.transaction_amount.amount');
        $this->transactionCurrency = data_get($rawData, 'network.transaction_amount.currency');
        $this->details = data_get($rawData, 'details.header');
    }

    public function id(): string
    {
        return $this->id;
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

    public function status()
    {
        return $this->status;
    }

    public function amountValue()
    {
        return $this->amountValue;
    }

    public function amountCurrency()
    {
        return $this->amountCurrency;
    }

    public function nativeValue()
    {
        return $this->nativeValue;
    }

    public function nativeCurrency()
    {
        return $this->nativeCurrency;
    }

    public function description()
    {
        return $this->description;
    }

    public function createdAt()
    {
        return $this->createdAt;
    }

    public function updatedAt()
    {
        return $this->updatedAt;
    }

    public function transactionUrl()
    {
        return $this->transactionUrl;
    }

    public function transactionHash()
    {
        return $this->transactionHash;
    }

    public function transactionFeeAmount()
    {
        return $this->transactionFeeAmount;
    }

    public function transactionFeeCurrency()
    {
        return $this->transactionFeeCurrency;
    }

    public function transactionAmount()
    {
        return $this->transactionAmount;
    }

    public function transactionCurrency()
    {
        return $this->transactionCurrency;
    }

    public function details()
    {
        return $this->details;
    }
}

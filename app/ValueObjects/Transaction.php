<?php

namespace App\ValueObjects;

use App\Enums\TransactionType;
use Carbon\CarbonImmutable;

class Transaction
{
    public TransactionType $type;

    public ?Amount $buyAmount = null;

    public ?Amount $sellAmount = null;

    public ?Amount $fee = null;

    public string $id;

    public string $status;

    public ?string $txHash = null;

    public ?string $txUrl = null;

    public string $notes;

    public array $rawData;

    public ?CarbonImmutable $txDate;

    public function __construct(array $rawData)
    {
        $this
            ->setRawData($rawData)
            ->setId($this->getRaw('id'))
            ->setStatus($this->getRaw('status'))
            ->setTxDate(CarbonImmutable::make($this->getRaw('created_at')));
    }

    public function setTxDate(?CarbonImmutable $txDate): static
    {
        $this->txDate = $txDate;

        return $this;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setRawData(array $rawData): static
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getRaw($key = null, $default = null): mixed
    {
        return data_get($this->rawData, $key, $default);
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setSellAmount(Amount $sellAmount): static
    {
        $this->sellAmount = $sellAmount;

        return $this;
    }

    public function setBuyAmount(Amount $buyAmount): static
    {
        $this->buyAmount = $buyAmount;

        return $this;
    }

    public function setFee($fee): static
    {
        $this->fee = $fee;

        return $this;
    }

    public function setTxHash(?string $txHash): static
    {
        $this->txHash = $txHash;

        return $this;
    }

    public function setTxUrl(?string $txUrl): static
    {
        $this->txUrl = $txUrl;

        return $this;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}

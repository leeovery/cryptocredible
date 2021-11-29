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

    public ?string $status = null;

    public ?string $txHash = null;

    public ?string $txUrl = null;

    public string $notes = '';

    public ?CarbonImmutable $date;

    public function __construct(private array $rawData)
    {
    }

    public function setDate(CarbonImmutable|string|null $date): static
    {
        if (is_string($date)) {
            $date = CarbonImmutable::make($date);
        }

        $this->date = $date;

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

    public function setRawData(array $array): static
    {
        $this->notes = $notes;

        return $this;
    }
}

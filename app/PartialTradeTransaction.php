<?php

namespace App;

use App\Enums\TradeSide;
use JetBrains\PhpStorm\Pure;

class PartialTradeTransaction extends Transaction
{
    public string $matchableId;

    public TradeSide $tradeSide;

    #[Pure]
    public static function createFromTransaction(Transaction $transaction): static
    {
        $partialTx = new self();
        $objValues = get_object_vars($transaction);
        foreach ($objValues as $key => $value) {
            $partialTx->$key = $value;
        }

        return $partialTx;
    }

    public function setMatchableId(string $matchableId): static
    {
        $this->matchableId = $matchableId;

        return $this;
    }

    public function setTradeSide(Enums\TradeSide $tradeSide): static
    {
        $this->tradeSide = $tradeSide;

        return $this;
    }
}

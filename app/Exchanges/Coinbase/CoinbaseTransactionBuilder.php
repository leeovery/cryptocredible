<?php

namespace App\Exchanges\Coinbase;

use App\Contracts\TransactionBuilder;
use App\Enums\TransactionType;
use App\Exchanges\Coinbase\Mappers\BuyTxMapper;
use App\Exchanges\Coinbase\Mappers\ExchangeDepositTxMapper;
use App\Exchanges\Coinbase\Mappers\ExchangeWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\FiatDepositTxMapper;
use App\Exchanges\Coinbase\Mappers\FiatWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\InterestTxMapper;
use App\Exchanges\Coinbase\Mappers\ProDepositTxMapper;
use App\Exchanges\Coinbase\Mappers\ProWithdrawalTxMapper;
use App\Exchanges\Coinbase\Mappers\SellTxMapper;
use App\Exchanges\Coinbase\Mappers\SendTxMapper;
use App\Exchanges\Coinbase\Mappers\TradeTxMapper;
use App\Exchanges\Coinbase\Mappers\TxMapper;
use App\Transaction;
use Illuminate\Support\Carbon;

class CoinbaseTransactionBuilder implements TransactionBuilder
{
    private array $rawData;

    public function build(array $rawTxData): Transaction
    {
        $this->rawData = $rawTxData;

        // THOUGHTS....
        // * do we need to check status??
        // * get payment methods to know when crypto has been purchased with a debit card, so we can
        //   create a fiat deposit to balance things.

        return $this->getMapperForTxType()
            ->execute((new Transaction)
                ->setRawData($this->rawData)
                ->setId($this->getRaw('id'))
                ->setStatus($this->getRaw('status'))
                ->setTxDate(Carbon::make($this->getRaw('created_at')))
            );
    }

    private function getMapperForTxType(): TxMapper
    {
        $lookup = [
            'buy'                 => BuyTxMapper::class,
            'sell'                => SellTxMapper::class,
            'trade'               => TradeTxMapper::class,
            'send'                => SendTxMapper::class,
            'pro_deposit'         => ProDepositTxMapper::class,
            'pro_withdrawal'      => ProWithdrawalTxMapper::class,
            'exchange_deposit'    => ExchangeDepositTxMapper::class,
            'exchange_withdrawal' => ExchangeWithdrawalTxMapper::class,
            'fiat_deposit'        => FiatDepositTxMapper::class,
            'fiat_withdrawal'     => FiatWithdrawalTxMapper::class,
            'interest'            => InterestTxMapper::class,
        ];

        $type = $this->getRaw('type');
        $mapper = $lookup[$type] ?? abort(400, "No matching tx mapper found for coinbase tx type '{$type}'.");

        return new $mapper($this->getRaw());
    }

    private function getRaw($key = null): mixed
    {
        return data_get($this->rawData, $key);
    }
}

<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionProcessor;
use App\Exchanges\Coinbase\DataMappers\BuyTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\ExchangeDepositTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\ExchangeWithdrawalTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\FiatDepositTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\FiatWithdrawalTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\InterestTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\ProDepositTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\ProWithdrawalTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\SellTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\SendTransactionDataMapper;
use App\Exchanges\Coinbase\DataMappers\TradeTransactionDataMapper;
use App\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MapRawDataToTransactionProcessor implements TransactionProcessor
{
    private array $dataMappers = [
        'buy'                 => BuyTransactionDataMapper::class,
        'sell'                => SellTransactionDataMapper::class,
        'trade'               => TradeTransactionDataMapper::class,
        'send'                => SendTransactionDataMapper::class,
        'pro_deposit'         => ProDepositTransactionDataMapper::class,
        'pro_withdrawal'      => ProWithdrawalTransactionDataMapper::class,
        'exchange_deposit'    => ExchangeDepositTransactionDataMapper::class,
        'exchange_withdrawal' => ExchangeWithdrawalTransactionDataMapper::class,
        'fiat_deposit'        => FiatDepositTransactionDataMapper::class,
        'fiat_withdrawal'     => FiatWithdrawalTransactionDataMapper::class,
        'interest'            => InterestTransactionDataMapper::class,
    ];

    public function handle(Collection $transactions): Collection
    {
        // THOUGHTS....
        // * do we need to check status??
        // * get payment methods to know when crypto has been purchased with a debit card, so we can
        //   create a fiat deposit to balance things.

        return $transactions->map(function (array $transaction) {
            return with($this->getMapper(data_get($transaction, 'type')), function (string $mapper) use ($transaction) {
                return new $mapper($transaction);
            })->execute((new Transaction)
                ->setRawData($transaction)
                ->setId(data_get($transaction, 'id'))
                ->setStatus(data_get($transaction, 'status'))
                ->setTxDate(Carbon::make(data_get($transaction, 'created_at')))
            );
        });
    }

    private function getMapper(string $type): string
    {
        return $this->dataMappers[$type]
            ?? abort(400, "No matching tx mapper found for coinbase tx type '{$type}'.");
    }
}

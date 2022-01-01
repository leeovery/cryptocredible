<?php

namespace App\Exchanges\Coinbase\Processors;

use App\Contracts\TransactionDataMapper;
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
use App\ValueObjects\Transaction;
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

    public function handle(Collection $transactions, callable $next): Collection
    {
        return $next(
            $transactions->map(function (array $transaction) {
                return $this
                    ->getMapperByType(data_get($transaction, 'type'))
                    ->execute(new Transaction($transaction));
            })
        );
    }

    private function getMapperByType(string $type): TransactionDataMapper
    {
        $mapper = $this->dataMappers[$type]
            ?? abort(400, "No matching tx mapper found for coinbase tx type '{$type}'.");

        return new $mapper;
    }
}

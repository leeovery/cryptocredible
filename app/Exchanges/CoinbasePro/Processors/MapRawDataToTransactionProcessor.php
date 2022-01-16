<?php

namespace App\Exchanges\CoinbasePro\Processors;

use App\Contracts\TransactionDataMapper;
use App\Contracts\TransactionProcessor;
use App\Exchanges\CoinbasePro\DataMappers\ConversionTransactionDataMapper;
use App\Exchanges\CoinbasePro\DataMappers\FeeTransactionDataMapper;
use App\Exchanges\CoinbasePro\DataMappers\MatchTransactionDataMapper;
use App\Exchanges\CoinbasePro\DataMappers\RebateTransactionDataMapper;
use App\Exchanges\CoinbasePro\DataMappers\TransferTransactionDataMapper;
use App\ValueObjects\Transaction;
use Illuminate\Support\Collection;

class MapRawDataToTransactionProcessor implements TransactionProcessor
{
    private array $dataMappers = [
        'transfer'   => TransferTransactionDataMapper::class,
        'match'      => MatchTransactionDataMapper::class,
        'fee'        => FeeTransactionDataMapper::class,
        'rebate'     => RebateTransactionDataMapper::class,
        'conversion' => ConversionTransactionDataMapper::class,
    ];

    public function handle(Collection $transactions, callable $next): Collection
    {
        return $next(
            $transactions->map(
                fn(array $transaction) => $this
                    ->getMapperByType(data_get($transaction, 'type'))
                    ->execute(new Transaction($transaction))
            )
        );
    }

    private function getMapperByType(string $type): TransactionDataMapper
    {
        $mapper = $this->dataMappers[$type]
            ?? abort(400, "No matching tx mapper found for coinbase pro tx type '{$type}'.");

        return new $mapper;
    }
}

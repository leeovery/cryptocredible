<?php

namespace App\Commands;

use App\Exchanges\Binance\Facades\Binance;
use App\Exchanges\CoinbasePro\CoinbaseProWallet;
use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Managers\TransactionProcessManager;
use App\Services\Buzz\Facade\Buzz;
use Illuminate\Support\Collection;

class SyncBinance extends AbstractSyncCommand
{
    protected string $exchangeName = 'Binance';

    public function registerHandlers()
    {
        $this
            ->registerGetTransactionsHandler(fn () => $this->fetchBinanceTransactions())
            ->registerProcessTransactionsHandler(fn ($txs) => $this->processBinanceTransactions($txs));
    }

    private function fetchBinanceTransactions(): Collection
    {
        // deposit history
        // - https://binance-docs.github.io/apidocs/spot/en/#deposit-history-supporting-network-user_data
        // withdrawal history
        // - https://binance-docs.github.io/apidocs/spot/en/#withdraw-history-supporting-network-user_data
        // trade history
        //  - https://binance-docs.github.io/apidocs/spot/en/#account-trade-list-user_data
        // fiat deposit / withdrawals
        // - https://binance-docs.github.io/apidocs/spot/en/#get-fiat-deposit-withdraw-history-user_data
        // fiat payments - what are these?
        // - https://binance-docs.github.io/apidocs/spot/en/#get-fiat-payments-history-user_data
        // conversions
        // - https://binance-docs.github.io/apidocs/spot/en/#convert-endpoints

        // dump transactions into a tmp dir every x number of fetches
        // if stopped then continue from where left off (how?)
        // add arg to purge tmp files to start again

        $transactions = collect();

        $transactions->put('deposits', $this->runTask('Get deposit history', function () {
            return Binance::fetchDepositHistory();
        }));

        dd($transactions);

        $withdrawalHistory = $this->runTask('Get withdrawal history', function () {
            return Binance::fetchWithdrawalHistory();
        });

        dd($depositHistory->count(), $withdrawalHistory->count());

        $this->comment('Fetch transactions for:');


    }

    private function processBinanceTransactions(Collection $transactions): Collection
    {
        return TransactionProcessManager::binance()->process($transactions);
    }
}

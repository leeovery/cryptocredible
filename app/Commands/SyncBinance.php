<?php

namespace App\Commands;

use App\Exchanges\Binance\Facades\Binance;
use App\Exchanges\CoinbasePro\CoinbaseProAccount;
use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Managers\TransactionProcessManager;
use Illuminate\Support\Collection;

class SyncBinance extends AbstractSyncCommand
{
    protected string $exchangeName = 'Binance';

    public function registerHandlers()
    {
        $this
            ->registerFetchHandler(fn () => $this->fetchBinanceTransactions())
            ->registerProcessHandler(fn ($txs) => $this->processBinanceTransactions($txs));
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

        $depositHistory = collect();
        $this->task('Fetch deposit history', function () use (&$depositHistory) {
            $depositHistory = Binance::fetchDepositHistory();
            dd($depositHistory);
        });

        dd('---');

        $this->comment('Fetch transactions for:');

        return $accounts->flatMap(function (CoinbaseProAccount $account) {
            $transactions = collect();
            $this->task("    {$account->currency()} Wallet", function () use ($account, &$transactions) {
                $transactions = CoinbasePro::fetchAllTransactions($account)
                    ->map(fn (array $tx) => tap($tx, fn (&$tx) => $tx['currency'] = $account->currency()));
            }, 'fetching...');

            return $transactions;
        })->filter();
    }

    private function processBinanceTransactions(Collection $transactions): Collection
    {
        return TransactionProcessManager::coinbasePro()->process($transactions);
    }
}

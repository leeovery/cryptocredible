<?php

namespace App\Commands;

use App\Exchanges\Coinbase\CoinbaseAccount;
use App\Exchanges\Coinbase\Facades\Coinbase;
use App\Managers\TransactionProcessManager;
use Illuminate\Support\Collection;

class SyncCoinbase extends AbstractSyncCommand
{
    protected string $exchangeName = 'Coinbase';

    public function registerHandlers()
    {
        $this
            ->registerFetchHandler(fn () => $this->fetchCoinbaseTransactions())
            ->registerProcessHandler(fn ($txs) => $this->processCoinbaseTransactions($txs));
    }

    private function processCoinbaseTransactions(Collection $transactions): Collection
    {
        return TransactionProcessManager::coinbase()->process($transactions);
    }

    private function fetchCoinbaseTransactions(): Collection
    {
        $accounts = collect();
        $this->task('Open Coinbase connection', function () use (&$accounts) {
            $accounts = Coinbase::fetchAllAccounts();
        });

        $this->comment('Fetch transactions for:');

        return $accounts->flatMap(function (CoinbaseAccount $account) {
            $transactions = collect();
            $this->task("    {$account->name()}", function () use ($account, &$transactions) {
                $transactions = Coinbase::fetchAllTransactions($account);
            }, 'fetching...');

            return $transactions;
        })->filter();
    }
}

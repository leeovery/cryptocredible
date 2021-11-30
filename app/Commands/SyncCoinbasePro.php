<?php

namespace App\Commands;

use App\Exchanges\CoinbasePro\CoinbaseProAccount;
use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Managers\TransactionProcessManager;
use Illuminate\Support\Collection;

class SyncCoinbasePro extends AbstractSyncCommand
{
    protected string $exchangeName = 'Coinbase Pro';

    public function registerHandlers()
    {
        $this
            ->registerFetchHandler(fn () => $this->fetchCoinbaseProTransactions())
            ->registerProcessHandler(fn ($txs) => $this->processCoinbaseProTransactions($txs));
    }

    private function fetchCoinbaseProTransactions(): Collection
    {
        $accounts = collect();
        $this->task('Open Coinbase Pro connection', function () use (&$accounts) {
            $accounts = CoinbasePro::fetchAllAccounts();
        });

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

    private function processCoinbaseProTransactions(Collection $transactions): Collection
    {
        return TransactionProcessManager::coinbasePro()->process($transactions);
    }
}

<?php

namespace App\Commands;

use App\Exchanges\Coinbase\CoinbaseAccount;
use App\Exchanges\Coinbase\Facades\Coinbase;
use App\Managers\TransactionProcessManager;
use Illuminate\Support\Collection;

class SyncCoinbase extends SyncCommand
{
    protected string $exchangeName = 'Coinbase';

    public function handle()
    {
        $this->title($this->exchangeName);

        $this->outputTransactions(
            $this->processTransactions($this->fetchTransactions())
        );

        $this->info('Output successful ✅');
    }

    public function processTransactions(Collection $transactions): Collection
    {
        $this->info('Process transactions...');
        $this->task('Normalise data & match up trades', function () use (&$transactions) {
            $transactions = TransactionProcessManager::coinbase()->process($transactions);
        });

        return $transactions;
    }

    private function fetchTransactions(): Collection
    {
        $this->info('Fetch transactions...');

        if ($transactions = $this->getTransactionListFromProvidedFile()) {
            return $transactions;
        }

        $accounts = collect();
        $this->task('Open Coinbase connection', function () use (&$accounts) {
            $accounts = Coinbase::fetchAllAccounts();
        });

        $this->line('Fetch transactions for:');
        $transactions = $accounts->flatMap(function (CoinbaseAccount $account) {
            $transactions = collect();
            $this->task("    {$account->name()}", function () use ($account, &$transactions) {
                $transactions = Coinbase::fetchAllTransactions($account);
            }, 'fetching...');

            return $transactions;
        })->filter();

        $this->dumpTransactionsToFile($transactions);

        $this->newLine();

        return $transactions;
    }
}

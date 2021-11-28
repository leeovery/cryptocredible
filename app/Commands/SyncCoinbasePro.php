<?php

namespace App\Commands;

use App\Exchanges\CoinbasePro\CoinbaseProAccount;
use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Facades\TransactionOutputManager;
use App\Managers\TransactionProcessManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;

class SyncCoinbasePro extends SyncCommand
{
    protected $signature = 'sync:coinbase-pro
                            {--o|output-dir=./../ : Provide a dir on local file system to output csv to.}
                            {--j|json= : Provide a json file rather than fetch txs via api.}
                            {--dump : Dump all the transactions fetched via the api into a json file.}';

    protected $description = 'Fetch, process and output all Coinbase Pro transactions in a format suitable for processing with BittyTax.';

    public function handle()
    {
        $this->outputTitle('Coinbase Pro');

        $this->processTransactions(
            $this->fetchTransactions()
        );

        $this->info('Output successful ✅');
    }

    public function processTransactions(Collection $transactions): void
    {
        $this->info('Process transactions...');

        $this->task('Normalise data & match up trades', function () use (&$transactions) {
            $transactions = TransactionProcessManager::coinbasePro()->process($transactions);
        });

        $this->task('Output data', function () use ($transactions) {
            TransactionOutputManager::run($transactions, 'Coinbase', $this->option('output-dir'));
        });

        $this->newLine();
    }

    private function fetchTransactions(): Collection
    {
        $this->info('Fetch transactions...');

        if (! is_null($this->option('json'))) {
            return $this->getTransactionListFromProvidedFile($this->option('json'));
        }

        $accounts = collect();
        $this->task('Open Coinbase Pro connection', function () use (&$accounts) {
            $accounts = CoinbasePro::fetchAllAccounts();
        });

        $this->line('Fetch transactions for:');
        $transactions = $accounts->flatMap(function (CoinbaseProAccount $account) {
            $transactions = collect();
            $this->task("    {$account->currency()} Wallet", function () use ($account, &$transactions) {
                $transactions = CoinbasePro::fetchAllTransactions($account);
            }, 'fetching...');

            return $transactions;
        })->filter();

        $this->dumpTransactionsToFile($transactions, 'coinbase-pro-transactions');

        $this->newLine();

        return $transactions;
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

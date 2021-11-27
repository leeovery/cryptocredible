<?php

namespace App\Commands;

use App\Exchanges\Coinbase\CoinbaseAccount;
use App\Exchanges\Coinbase\Facades\Coinbase;
use App\OutputManager;
use App\Transaction;
use App\TransactionManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class SyncCoinbase extends Command
{
    protected $signature = 'sync:coinbase
                            {--o|output-dir=./../ : Provide a dir on local file system to output csv to.}
                            {--j|json= : Provide a json file rather than fetch txs via api.}
                            {--dump : Dump all the transactions fetched via the api into a json file.}';

    protected $description = 'Fetch, process and output all Coinbase transactions in a format suitable for processing with BittyTax.';

    public function handle()
    {
        $this->newLine();
        $this->info('**********************');
        $this->info('****** Coinbase ******');
        $this->info('**********************');
        $this->newLine();

        $this->processTransactions(
            $this->fetchTransactions()
        );

        $this->info('Output successful ✅');
    }

    public function processTransactions(Collection $transactions): void
    {
        $this->info('Process transactions...');

        $this->task('Normalise data & match up trades', function () use (&$transactions) {
            $transactions = TransactionManager::coinbase()->process($transactions);
        });
        
        $this->task('Output data', function () use ($transactions) {
            OutputManager::run(
                $transactions,
                'Coinbase',
                $this->option('output-dir')
            );
        });
        $this->newLine();
    }

    private function fetchTransactions(): Collection
    {
        $this->info('Fetch transactions...');
        $transactions = collect();

        if (! is_null($this->option('json'))) {
            $this->task('Use provided json file', function () use (&$transactions) {
                $transactions = collect(json_decode(file_get_contents($this->option('json')), true));
            });

            if ($transactions->isEmpty()) {
                abort(404, 'No transactions found in the provided file.');
            }

            $this->newLine();

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

    private function dumpTransactionsToFile(Collection $transactions): void
    {
        if ($this->option('dump')) {
            $this->line('Dump transactions to file option provided...');
            $outputDir = str($this->option('output-dir'))->finish('/')->append('coinbase-transactions.json');
            $this->task("Dumping data to {$outputDir}", function () use ($outputDir, $transactions) {
                file_put_contents($outputDir, $transactions->toJson());
            });
        }
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Coinbase;
use App\Exchanges\Coinbase\CoinbaseAccount;
use App\OutputManager;
use App\TransactionManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class SyncCoinbase extends Command
{
    protected $signature = 'sync:coinbase
                            {--o|output-dir=./ : Provide a dir on local file system to output csv to.}
                            {--j|json= : Provide a json file rather than fetch txs via api.}
                            {--dump-json : Dump all the transactions fetched via the api into a json file.}';

    protected $description = 'Command description';

    protected Coinbase $coinbase;

    public function handle(Coinbase $coinbase)
    {
        $this->coinbase = $coinbase;

        $this->newLine();
        $this->info('**********************');
        $this->info('****** Coinbase ******');
        $this->info('**********************');
        $this->newLine();

        $transactions = $this->fetchTransactions();
        $this->newLine();

        $this->info('Process transactions...');

        $this->task('Output data', function () use ($transactions) {
            OutputManager::run(
                TransactionManager::coinbase()->process($transactions),
                'Coinbase',
                $this->option('output-dir')
            );
        });
        $this->newLine();

        $this->info('Done ✅');
    }

    private function fetchTransactions(): Collection
    {
        $this->info('Fetch transactions...');
        $transactions = collect();

        if (! is_null($this->option('json'))) {
            $this->task('Using provided json file for tx list', function () use (&$transactions) {
                $transactions = collect(json_decode(file_get_contents($this->option('json')), true));
            });

            if ($transactions->isEmpty()) {
                abort(404, 'No transactions found in the provided file.');
            }

            return $transactions;
        }

        $accounts = collect();
        $this->task('Open Coinbase connection', function () use (&$accounts) {
            $accounts = $this->coinbase->fetchAllAccounts();
        });

        $this->line('Fetch transactions for:');
        $accounts->each(function (CoinbaseAccount $account) use ($transactions) {
            $this->task("    {$account->name()}", function () use ($transactions, $account) {
                return $this->coinbase
                    ->fetchAllTransactions($account)
                    ->whenNotEmpty(function ($results) use ($transactions) {
                        $transactions->push(...$results);
                    });
            });
        });

        if ($this->option('dump-json')) {
            Storage::put("/transactions.json", $transactions->toJson());
        }

        return $transactions;
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

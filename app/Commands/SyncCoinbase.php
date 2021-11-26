<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Coinbase;
use App\Exchanges\Coinbase\CoinbaseAccount;
use App\OutputManager;
use App\Transaction;
use App\TransactionManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Writer;

class SyncCoinbase extends Command
{
    protected $signature = 'sync:coinbase
                            {--j|json= : Provide a json file rather than fetch txs via api.}
                            {--dump-json : Dump all the transactions fetched via the api into a json file.}';

    protected $description = 'Command description';

    protected Collection $accounts;

    protected Collection $transactions;

    protected Coinbase $coinbase;

    public function handle(Coinbase $coinbase)
    {
        $this->newLine();
        $this->info('*** Coinbase ***');
        $this->newLine(2);

        $this->coinbase = $coinbase;
        $this->transactions = collect();

        if (! is_null($this->option('json'))) {
            $this->info('Using provided json file for tx list...');
            $this->transactions = collect(json_decode(Storage::get("/transactions.json"), true));
        } else {
            $this->info('Coinbase connection opened...');
            $this->accounts = $this->coinbase->fetchAllAccounts();
            $this->newLine();

            $this->info('Fetching transactions for:');
            $this->accounts->each(function (CoinbaseAccount $account) {
                $this->task("{$account->name()}", function () use ($account) {
                    $this->coinbase
                        ->fetchAllTransactions($account)
                        ->whenNotEmpty(function ($results) {
                            $this->transactions->push(...$results);
                        });
                });
            });
            $this->newLine();

            if ($this->option('dump-json')) {
                Storage::put("/transactions.json", $this->transactions->toJson());
            }
        }

        $this->info('Normalise, match and check raw tx data:');
        $this->transactions = TransactionManager::coinbase()->process($this->transactions);
        $this->newLine();

        OutputManager::run($this->transactions);
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

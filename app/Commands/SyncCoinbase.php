<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Coinbase;
use App\Exchanges\Coinbase\CoinbaseAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class SyncCoinbase extends Command
{
    protected $signature = 'sync:coinbase';

    protected $description = 'Command description';

    protected Collection $accounts;

    protected Collection $transactions;

    protected Coinbase $coinbase;

    public function handle(Coinbase $coinbase)
    {
        $this->coinbase = $coinbase;
        $this->accounts = collect();
        $this->transactions = collect();

        $this->info('Syncing with Coinbase...');
        $this->task('Fetch Coinbase accounts', function () {
            $this->accounts = $this->coinbase->fetchAllAccounts();
        });

        $this->info('Fetch transactions for each account');
        $this->accounts->each(function(CoinbaseAccount $account) {
            $this->task("-- {$account->name()}", function() use ($account) {
                $results = $this->coinbase->fetchAllTransactions($account);

                if ($results->isNotEmpty()) {
                    $this->transactions->push(...$results->all());
                }
            });


            if ($this->transactions->count() > 2) {
                dd($this->transactions);
            }
        });
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

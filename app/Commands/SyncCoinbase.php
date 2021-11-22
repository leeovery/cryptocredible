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

        $this->info('*** Coinbase connection opened ***');
        $this->accounts = $this->coinbase->fetchAllAccounts();

        $this->info('Fetching transactions for:');
        $this->accounts->each(function (CoinbaseAccount $account) {
            $this->task("{$account->name()}", function () use ($account) {
                $this->coinbase
                    ->fetchAllTransactions($account)
                    ->whenNotEmpty(function ($results) {
                        $this->transactions->push(...$results->all());
                    });
            });

            // if ($this->transactions->count() > 2) {
            //     dd($this->transactions);
            // }
        });

        dd($this->transactions);

        // transactions types
        // send
        // - positive amount.amount === deposit
        // - negative amount.amount === withdrawal

        // exchange_deposit
        // - neg. amount.amount = withdrawal to coinbase pro? or any exchange?
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

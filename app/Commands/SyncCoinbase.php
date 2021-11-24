<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Coinbase;
use App\Exchanges\Coinbase\CoinbaseAccount;
use App\Exchanges\Coinbase\CoinbaseAccountCollection;
use App\Exchanges\Coinbase\CoinbaseTransactionBuilder;
use App\Exchanges\Coinbase\CoinbaseTransactionCollection;
use App\TransactionDirector;
use App\TransactionFactory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $this->transactions = collect();

        $this->info('*** Coinbase connection opened ***');
        $this->accounts = $this->coinbase->fetchAllAccounts();

        $this->info('Fetching transactions for:');
        $this->accounts->each(function (CoinbaseAccount $account) {
            $this->task("{$account->name()}", function () use ($account) {

                $this->coinbase
                    ->fetchAllTransactions($account)
                    ->whenNotEmpty(function ($results) {
                        $this->transactions->push(...$results);
                    });

            });

            if ($this->transactions->count() > 10) {
                return false;
            }
        });

        // Add option to dump txs rather than process (or as well as)
        // Storage::put("/transactions.json", $this->transactions->toJson());
        // dd();

        // take collection of transactions
        // pass each one into a specific coinbase builder which will
        // map the coinbase raw data into a local Transaction class.
        // The director handles the mapping of the collection, passing each item
        // into the TransactionBuilder

        $transactions = TransactionDirector::coinbase()->mapCollection($this->transactions);

        dd($transactions);

        // normalise transactions to a standard format all exchanges can use...

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

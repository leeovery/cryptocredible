<?php

namespace App\Commands;

use App\Exchanges\CoinbasePro\CoinbaseProAccount;
use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Managers\TransactionProcessManager;
use Illuminate\Support\Collection;

class SyncCoinbasePro extends SyncCommand
{
    protected string $exchangeName = 'Coinbase Pro';

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
            $transactions = TransactionProcessManager::coinbasePro()->process($transactions);
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
        $this->task('Open Coinbase Pro connection', function () use (&$accounts) {
            $accounts = CoinbasePro::fetchAllAccounts();
        });

        $this->line('Fetch transactions for:');
        $transactions = $accounts->flatMap(function (CoinbaseProAccount $account) {
            $transactions = collect();
            $this->task("    {$account->currency()} Wallet", function () use ($account, &$transactions) {
                $transactions = CoinbasePro::fetchAllTransactions($account)
                    ->map(fn (array $tx) => tap($tx, fn (&$tx) => $tx['currency'] = $account->currency()));
            }, 'fetching...');

            return $transactions;
        })->filter();

        $this->dumpTransactionsToFile($transactions);

        $this->newLine();

        return $transactions;
    }
}

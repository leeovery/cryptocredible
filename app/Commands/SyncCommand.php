<?php

namespace App\Commands;

use App\Facades\TransactionOutputManager;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class SyncCommand extends Command
{
    protected string $exchangeName = '';

    protected $description = '';

    public function __construct()
    {
        $this->setName('sync:'.str($this->exchangeName)->lower()->kebab());
        $this->description = "Fetch, process and output all {$this->exchangeName} transactions in a format suitable for processing with BittyTax.";
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'output-dir',
                '--o',
                InputOption::VALUE_OPTIONAL,
                'Provide a dir on local file system to output csv to.',
                './../'
            )
            ->addOption(
                'json',
                '--j',
                InputOption::VALUE_OPTIONAL,
                'Provide a json file rather than fetch txs via api.'
            )
            ->addOption(
                'dump',
                null,
                InputOption::VALUE_NONE,
                'Dump all the transactions fetched via the api into a json file.'
            );
    }

    protected function dumpTransactionsToFile(Collection $transactions): void
    {
        if ($this->option('dump')) {
            $this->line('Dump transactions to file option provided...');
            $outputDir = str($this->option('output-dir'))->finish('/')->append($this->exchangeName.'-transaction-dump-'.now().'.json');
            $this->task("Dumping data to {$outputDir}", function () use ($outputDir, $transactions) {
                file_put_contents($outputDir, $transactions->toJson());
            });
        }
    }

    protected function outputTransactions(Collection $transactions): void
    {
        $this->task('Output data', function () use ($transactions) {
            TransactionOutputManager::run($transactions, $this->exchangeName, $this->option('output-dir'));
        });
    }

    protected function getTransactionListFromProvidedFile(): ?Collection
    {
        if (is_null($file = $this->option('json'))) {
            return null;
        }

        $transactions = collect();

        $this->task('Use provided json file', function () use ($file, &$transactions) {
            $transactions = collect(json_decode(file_get_contents($file), true));
        });

        if ($transactions->isEmpty()) {
            abort(404, 'No transactions found in the provided file.');
        }

        $this->newLine();

        return $transactions;
    }
}

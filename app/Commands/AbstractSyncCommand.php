<?php

namespace App\Commands;

use App\Facades\TransactionOutputManager;
use Closure;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractSyncCommand extends BaseCommand
{
    protected string $exchangeName = '';

    protected $description = '';

    private Closure $processTransactionsHandler;

    private Closure $getTransactionsHandler;

    public function __construct()
    {
        $this->setName('sync:'.str($this->exchangeName)->lower()->kebab());
        $this->description = "Fetch, process and output all {$this->exchangeName} transactions in a format suitable for processing with BittyTax.";
        parent::__construct();
    }

    public function handle()
    {
        $this->registerHandlers();

        $this->title($this->exchangeName);

        $this->outputTransactions(
            $this->processTransactions(
                $this->fetchTransactions()
            )
        );

        $this->info('Output successful ✅');
    }

    abstract public function registerHandlers();

    protected function outputTransactions(Collection $transactions): void
    {
        $this->alert('Output data');
        $this->runTask('Steaming output to csv file', function () use ($transactions) {
            TransactionOutputManager::run($transactions, $this->exchangeName, $this->option('output-dir'));
        }, 'streaming...');
        $this->newLine(2);
    }

    protected function processTransactions(Collection $transactions): Collection
    {
        $this->alert('Process transactions');
        $transactions = $this->runTask('Normalise data & match up trades', function () use ($transactions) {
            return ($this->processTransactionsHandler)($transactions);
        }, 'normalising...');
        $this->newLine(2);

        return $transactions;
    }

    protected function fetchTransactions(): Collection
    {
        $this->alert('Fetch transactions');
        if ($transactions = $this->getTransactionListFromProvidedFile()) {
            return $transactions;
        }

        $transactions = ($this->getTransactionsHandler)();

        $this->dumpTransactionsToFile($transactions);

        $this->newLine(2);

        return $transactions;
    }

    protected function getTransactionListFromProvidedFile(): ?Collection
    {
        if (is_null($file = $this->option('json'))) {
            return null;
        }

        $transactions = $this->runTask('Use provided json file', function () use ($file) {
            return collect(json_decode(file_get_contents($file), true));
        });

        if ($transactions->isEmpty()) {
            abort(404, 'No transactions found in the provided file.');
        }

        $this->newLine();

        return $transactions;
    }

    protected function dumpTransactionsToFile(Collection $transactions): void
    {
        if ($this->option('dump')) {
            $outputDir = str($this->option('output-dir'))
                ->finish('/')
                ->append($this->exchangeName.'-transaction-dump-'.now().'.json')
                ->lower();
            $this->newLine();

            $this->runTask("Dumping data to {$outputDir}", function () use ($outputDir, $transactions) {
                file_put_contents($outputDir, $transactions->toJson());
            }, '⏬ dumping...');
        }
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
                'Provide a json file rather than fetch transactions via api.'
            )
            ->addOption(
                'dump',
                null,
                InputOption::VALUE_NONE,
                'Dump all the transactions fetched via the api into a json file.'
            );
    }

    protected function registerProcessTransactionsHandler(Closure $callable): self
    {
        $this->processTransactionsHandler = $callable;

        return $this;
    }

    protected function registerGetTransactionsHandler(Closure $callable): self
    {
        $this->getTransactionsHandler = $callable;

        return $this;
    }
}

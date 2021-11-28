<?php

namespace App\Commands;

use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

abstract class SyncCommand extends Command
{
    protected function outputTitle(string $title)
    {
        $this->newLine();
        $this->info('**********************');
        $this->info('**** '.$title.' ****');
        $this->info('**********************');
        $this->newLine();
    }

    protected function dumpTransactionsToFile(Collection $transactions, $fileName): void
    {
        if ($this->option('dump')) {
            $this->line('Dump transactions to file option provided...');
            $outputDir = str($this->option('output-dir'))->finish('/')->append($fileName.'.json');
            $this->task("Dumping data to {$outputDir}", function () use ($outputDir, $transactions) {
                file_put_contents($outputDir, $transactions->toJson());
            });
        }
    }

    protected function getTransactionListFromProvidedFile($file): Collection
    {
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

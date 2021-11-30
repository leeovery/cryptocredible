<?php

namespace App\Managers;

use App\ValueObjects\Transaction;
use Illuminate\Support\Collection;
use League\Csv\Writer;
use function str;

class TransactionOutputManager
{
    private array $headers = [
        'Type',
        'Buy Quantity', 'Buy Asset', 'Buy Value in GBP',
        'Sell Quantity', 'Sell Asset', 'Sell Value in GBP',
        'Fee Quantity', 'Fee Asset', 'Fee Value in GBP',
        'Wallet', 'Timestamp', 'Note', 'Raw Data',
    ];

    public function run(Collection $transactions, string $walletName, string $outputDir)
    {
        $transactions = $transactions
            ->sortBy(fn (Transaction $transaction) => $transaction->date->timestamp)
            ->values();

        $fileName = $this->buildOutputFileName($transactions, $walletName);
        $outputDir = str($outputDir)->finish('/');

        $csv = Writer::createFromPath($outputDir.$fileName.'.csv', 'w+');
        $csv->insertOne($this->headers);
        $csv->insertAll($transactions->map(fn (Transaction $transaction) => [
            $transaction->type->description,
            $transaction->buyAmount?->value ?? '',
            $transaction->buyAmount?->currency ?? '',
            '',
            $transaction->sellAmount?->value ?? '',
            $transaction->sellAmount?->currency ?? '',
            '',
            $transaction->fee?->value ?? '',
            $transaction->fee?->currency ?? '',
            '',
            $walletName,
            $transaction->date->utc()->format('Y-m-d H:i:s'),
            $transaction->notes,
            json_encode($transaction->getRaw()),
        ]));
    }

    private function buildOutputFileName(Collection $transactions, string $walletName): string
    {
        /** @var \App\ValueObjects\Transaction $firstTx */
        $firstTx = $transactions->first();
        /** @var \App\ValueObjects\Transaction $lastTx */
        $lastTx = $transactions->last();

        return sprintf(
            '%s_transactions_%s-%s',
            $walletName,
            $firstTx->date->toDateString(),
            $lastTx->date->toDateString()
        );
    }
}

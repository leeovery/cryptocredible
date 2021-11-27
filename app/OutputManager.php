<?php

namespace App;

use Illuminate\Support\Collection;
use League\Csv\Writer;

class OutputManager
{
    public static function run(Collection $transactions, string $walletName, string $outputDir)
    {
        $transactions = $transactions
            ->sortBy(fn(Transaction $transaction) => $transaction->txDate->timestamp)
            ->map(fn(Transaction $transaction) => [
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
                $transaction->txDate->utc()->format('Y-m-d H:i:s'),
                $transaction->notes,
                json_encode($transaction->rawData),
            ]);

        $header = [
            'Type',
            'Buy Quantity', 'Buy Asset', 'Buy Value in GBP',
            'Sell Quantity', 'Sell Asset', 'Sell Value in GBP',
            'Fee Quantity', 'Fee Asset', 'Fee Value in GBP',
            'Wallet', 'Timestamp', 'Note', 'Raw Data',
        ];

        // build file name by getting first and last date from transaction list.

        $outputDir = str($outputDir)->finish('/');
        $csv = Writer::createFromPath($outputDir.'coinbase-transactions.csv', 'w+');
        $csv->insertOne($header);
        $csv->insertAll($transactions);
    }
}

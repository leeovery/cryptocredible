<?php

namespace App\Exchanges\Coinbase;

use App\SyncingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class Coinbase implements SyncingService
{
    private Collection $accounts;
    private Collection $transactions;

    public function __construct(private string $baseUrl, private string $apiKey, private string $apiSecret) { }

    private function get($url): array
    {
        $timestamp = now()->timestamp;
        $url = Str::of($url)->remove('v2/')->start('/');
        $hash = sprintf('%s%s/v2%s%s', $timestamp, 'GET', $url, '');

        return Http::baseUrl($this->baseUrl)
            ->contentType('application/json')
            ->withHeaders([
                'CB-ACCESS-KEY'       => $this->apiKey,
                'CB-ACCESS-SIGN'      => hash_hmac('sha256', $hash, $this->apiSecret),
                'CB-ACCESS-TIMESTAMP' => $timestamp,
                'CB-VERSION' => '2021-11-21',
            ])
            ->get($url)
            ->json();
    }

    private function getAll(string $url): Collection
    {
        $collection = collect([]);

        while(true) {
            $results = $this->get($url);
            if ($data = data_get($results, 'data')) {
                $collection->push(...$data);
                $url = data_get($results, 'pagination.next_uri');
            } else {
                break;
            }
        }

        return $collection;
    }

    public function execute(Command $command)
    {
        $command->task('Fetch Coinbase accounts', function() {
            $this->accounts = $this->getAll('/accounts?limit=100');
        });

        $command->info('Fetch transactions for each account');

        // how can we use lazy collections here to reduce memory footprint
        // grab all accounts
        // then use LazyCollection to get transactions - how will this help??
        // only will help if were streaming to output, otherwise its still pulling them into mem.

        $this->transactions = collect();
        $this->accounts->each(function($account) use ($command) {
            $command->task("-- {$account['name']}", function() use ($account) {
                $results = $this->getAll("{$account['resource_path']}/transactions?expand=all&limit=100");

                if ($results->isNotEmpty()) {
                    $this->transactions->push(...$results->all());
                }
            });

            if ($this->transactions->count() > 4) {
                dd($this->transactions);
            }
        });
    }
}

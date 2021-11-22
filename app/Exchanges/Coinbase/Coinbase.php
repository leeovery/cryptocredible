<?php

namespace App\Exchanges\Coinbase;

use App\SyncingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Coinbase implements SyncingService
{
    public function __construct(private string $baseUrl, private string $apiKey, private string $apiSecret) { }

    public function fetchAllAccounts(): Collection
    {
        return $this->getAll('/accounts?limit=100')->mapInto(CoinbaseAccount::class);
    }

    public function fetchAllTransactions(CoinbaseAccount $account): Collection
    {
        return $this
            ->getAll("{$account->resourcePath()}/transactions?expand=all&limit=100")
            ->mapInto(CoinbaseTransaction::class);
    }

    private function getAll(string $url): Collection
    {
        $collection = collect([]);

        while (true) {
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

    private function get($url): array
    {
        $url = Str::of($url)->remove('v2/')->start('/');

        return Http::baseUrl($this->baseUrl)
            ->contentType('application/json')
            ->withHeaders($this->buildRequestHeaders('GET', $url))
            ->get($url)
            ->json();
    }

    private function buildRequestHeaders(string $method, string $url): array
    {
        $timestamp = now()->timestamp;
        $url = Str::of($url)->remove('v2/')->start('/');
        $hash = sprintf('%s%s/v2%s%s', $timestamp, $method, $url, '');

        return [
            'CB-ACCESS-KEY'       => $this->apiKey,
            'CB-ACCESS-SIGN'      => hash_hmac('sha256', $hash, $this->apiSecret),
            'CB-ACCESS-TIMESTAMP' => $timestamp,
            'CB-VERSION'          => '2021-11-21',
        ];
    }

    public function execute(Command $command)
    {
        $command->task('Fetch Coinbase accounts', function () {
            $this->accounts = $this->getAll('/accounts?limit=100');
        });

        $command->info('Fetch transactions for each account');

        $this->transactions = collect();
        $this->accounts->each(function ($account) use ($command) {
            $command->task("-- {$account['name']}", function () use ($account) {
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

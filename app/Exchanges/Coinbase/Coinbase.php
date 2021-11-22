<?php

namespace App\Exchanges\Coinbase;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Coinbase
{
    public function __construct(private string $apiKey, private string $apiSecret) { }

    public function fetchAllAccounts(): Collection
    {
        $startAfter = '91824c35-396c-5420-9d75-c334aae25f49';

        return $this->getAll('/accounts?limit=100&starting_after='.$startAfter)->mapInto(CoinbaseAccount::class);
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

        return Http::baseUrl('https://api.coinbase.com/v2')
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

    public function fetchAllTransactions(CoinbaseAccount $account): Collection
    {
        return $this->getAll("{$account->resourcePath()}/transactions?expand=all&limit=100");
    }
}

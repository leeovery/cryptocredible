<?php

namespace App\Exchanges\Coinbase;

use App\Exchanges\Coinbase\Exceptions\CoinbaseException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Coinbase
{
    public function __construct(private string $apiKey, private string $apiSecret) { }

    public function fetchAllAccounts(): Collection
    {
        // return $this->getAll('/accounts?limit=100')
        //     ->mapInto(CoinbaseAccount::class);

        return $this->getAll('/accounts?limit=100&starting_after=91824c35-396c-5420-9d75-c334aae25f49')
            ->mapInto(CoinbaseAccount::class);
    }

    private function getAll(string $url): Collection
    {
        $collection = collect();

        while (true) {
            $response = $this->get($url);

            throw_unless($response->successful(), CoinbaseException::requestFailed());
            $results = $response->json();

            $url = data_get($results, 'pagination.next_uri');
            if ($data = data_get($results, 'data')) {
                $collection->push(...$data);
            }

            if (is_null($url)) {
                goto end;
            }
        }

        end:

        return $collection;
    }

    private function get($url): Response
    {
        $url = Str::of($url)->remove('v2/')->start('/');

        return Http::baseUrl('https://api.coinbase.com/v2')
            ->contentType('application/json')
            ->withHeaders($this->buildRequestHeaders('GET', $url))
            ->retry(3, 250)
            ->timeout(5)
            ->get($url);
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

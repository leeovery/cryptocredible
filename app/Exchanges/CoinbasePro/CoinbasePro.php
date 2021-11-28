<?php

namespace App\Exchanges\CoinbasePro;

use App\Exchanges\CoinbasePro\Exceptions\CoinbaseProException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoinbasePro
{
    public function __construct(private string $apiKey, private string $apiSecret, private string $apiPassphrase) { }

    public function fetchAllAccounts(): Collection
    {
        return $this->getAll('/accounts')->mapInto(CoinbaseProAccount::class);
    }

    private function getAll(string $url): Collection
    {
        $limit = 1000;
        $collection = collect();
        $afterCursor = null;

        while (true) {
            $queries = http_build_query([
                'after' => $afterCursor,
                'limit' => $limit,
            ]);

            $response = $this->get($url.($queries ? '?'.$queries : ''));

            throw_unless($response->successful(), CoinbaseProException::requestFailed());
            $results = $response->json();
            if ($results) {
                $collection->push(...$results);
            }

            $afterCursor = $response->header('CB-AFTER');
            if (! $afterCursor || count($results) < $limit) {
                goto end;
            }
        }

        end:

        return $collection;
    }

    private function get($url): Response
    {
        $url = Str::of($url)->start('/');

        return Http::baseUrl('https://api.pro.coinbase.com')
            ->contentType('application/json')
            ->withHeaders($this->buildRequestHeaders('GET', $url))
            ->retry(3, 250)
            ->timeout(5)
            ->get($url);
    }

    private function buildRequestHeaders(string $method, string $url): array
    {
        $timestamp = now()->timestamp;
        $hash = sprintf('%s%s%s', $timestamp, $method, $url);

        return [
            'CB-ACCESS-KEY'        => $this->apiKey,
            'CB-ACCESS-SIGN'       => base64_encode(hash_hmac('sha256', $hash, base64_decode($this->apiSecret), true)),
            'CB-ACCESS-TIMESTAMP'  => $timestamp,
            'CB-ACCESS-PASSPHRASE' => $this->apiPassphrase,
        ];
    }

    public function fetchAllTransactions(CoinbaseProAccount $account): Collection
    {
        return $this->getAll("/accounts/{$account->id()}/ledger");
    }
}

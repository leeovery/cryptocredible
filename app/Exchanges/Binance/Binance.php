<?php

namespace App\Exchanges\Binance;

use App\Exchanges\Coinbase\Exceptions\CoinbaseException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Binance
{
    public function __construct(private string $apiKey, private string $apiSecret)
    {
    }

    public function fetchDepositHistory(): Collection
    {
        // get data from now going back in 90 day segments
        // - how do we know when to stop fetching?
        // - we cant so keep fetching back to 1483228800000 (Sun 1 January 2017 00:00:00)
        // (If both startTime and endTime are sent, time between startTime and endTime must be less than 90 days)

        // startTime
        // endTime
        // limit=1000

        return $this->getAll('/sapi/v1/capital/deposit/hisrec')->mapInto(CoinbaseAccount::class);
    }

    private function getAll(string $url): Collection
    {
        $collection = collect();

        while (true) {
            $response = $this->get($url);

            throw_unless($response->successful(), CoinbaseException::requestFailed());
            $results = $response->json();

            dd($results);

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
        $url = Str::of($url)->start('/');
        $params = [
            'timestamp' => now()->getTimestampMs(),
        ];
        $signature = hash_hmac(
            'sha256',
            $query = http_build_query($params),
            $this->apiSecret
        );

        return Http::baseUrl('https://api.binance.com')
            ->contentType('application/json')
            ->withHeaders([
                'X-MBX-APIKEY' => $this->apiKey,
            ])
            ->retry(3, 250)
            ->timeout(5)
            ->get("{$url}?{$query}&signature={$signature}");
    }

    public function fetchAllTransactions(CoinbaseAccount $account): Collection
    {
        return $this->getAll("{$account->resourcePath()}/transactions?expand=all&limit=100");
    }
}

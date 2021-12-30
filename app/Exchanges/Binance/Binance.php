<?php

namespace App\Exchanges\Binance;

use App\Exchanges\Binance\Exceptions\BinanceException;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Binance
{
    private Carbon $baseStartDate;

    public function __construct(private string $apiKey, private string $apiSecret)
    {
        $this->baseStartDate = Carbon::createFromTimestampMsUTC(1483228800000);
    }

    public function fetchDepositHistory(): Collection
    {
        // get data from now going back in 90 day segments
        // - start 1483228800000 (Sun 1 January 2017 00:00:00)
        // - end now
        // (If both startTime and endTime are sent, time between startTime and endTime must be less than 90 days)

        // recvWindow = 10_000
        // status = 1
        // startTime
        // endTime
        // limit=1000

        $query = [
            'recvWindow' => 10_000,
            'status'     => 1,
            'limit'      => 1000,
        ];

        // endTime=1485281835381
        // "endTime" => 1491004800000

        // 7776000000
        // 7776000000
        // 90 days in ms

        // startTime=1625249835381
        // Fri 2 July 2021 19:17:15
        // endTime=1633025835381
        // Thu 30 September 2021 19:17:15

        $period = CarbonPeriod::since($this->baseStartDate)
            ->days(90)
            ->until(now());

        dd($period);

        $query = array_merge([
            'startTime' => $this->baseStartDate->getTimestampMs(),
            'endTime'   => $this->baseStartDate->addDays(90)->getTimestampMs(),
        ], $query);
        dd($query);

        $results = $this->get('/sapi/v1/capital/deposit/hisrec');

        dd($results);
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
            ->get("{$url}&{$query}&signature={$signature}");
    }

    public function fetchAllTransactions(CoinbaseAccount $account): Collection
    {
        return $this->getAll("{$account->resourcePath()}/transactions?expand=all&limit=100");
    }

    private function getAll(string $url): Collection
    {
        $collection = collect();

        while (true) {

            $response = $this->get($url);

            throw_unless($response->successful(), BinanceException::requestFailed());
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
}

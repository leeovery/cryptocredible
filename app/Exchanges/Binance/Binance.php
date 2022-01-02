<?php

namespace App\Exchanges\Binance;

use App\Services\Buzz\Facade\Buzz;
use Carbon\CarbonPeriod;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Binance
{
    private Carbon $binanceEpoch;

    public function __construct(private string $apiKey, private string $apiSecret)
    {
        $this->binanceEpoch = Carbon::createFromTimestampMsUTC(1483228800000);
    }

    public function fetchDepositHistory(): Collection
    {
        // "amount" => "0.08064386"
        // "coin" => "ETH"
        // "network" => "ETH"
        // "status" => 1
        // "address" => "0x01f120a054422b6bf4c0dd79e208fe9517dcabe3"
        // "addressTag" => ""
        // "txId" => "0x94dca6d26fe55e0a240ee5983f369fedb90b0dfefb31d291213cb455b2807b5f"
        // "insertTime" => 1515625493000
        // "confirmTimes" => "12/12"
        // "unlockConfirm" => 0
        // "walletType" => 0

        return $this->fetchUsingTimestamps('/sapi/v1/capital/deposit/hisrec', [
            'status' => 1,
        ]);
    }

    private function fetchUsingTimestamps(string $url, array $params = [], int $days = 90): Collection
    {
        Buzz::newLine();
        $progressBar = Buzz::progressBar();

        $responses = Http::pool(function (Pool $pool) use ($params, $days, $url, $progressBar) {
            $endTime = now();

            return collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days($days))
                ->reverse()
                ->tap(function ($items) use ($progressBar) {
                    $progressBar->start($items->count());
                })
                ->map(function (Carbon $period) use ($params, $url, $pool, $progressBar, &$endTime) {
                    $request = $this->getAsync($pool, $url, array_merge($params, [
                        'recvWindow' => 5_000,
                        'limit'      => 1_000,
                        'startTime'  => $period->getTimestampMs(),
                        'endTime'    => $endTime->getTimestampMs(),
                    ]))->then(function () use ($progressBar) {
                        $progressBar->advance();
                    });

                    $endTime = $period->subMillisecond();

                    return $request;
                });
        });

        return collect($responses)->flatMap(function (Response $response) {
            return $response->json();
        })->filter()->tap(function () use ($progressBar) {
            $progressBar->finish();
            Buzz::moveCursorUp(2)->eraseToEnd();
        });

        // Buzz::newLine();
        // $progressBar = Buzz::progressBar();
        //
        // $endTime = now();
        //
        // return collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days(90))
        //     ->reverse()
        //     ->tap(function ($items) use ($progressBar) {
        //         $progressBar->start($items->count());
        //     })
        //     ->flatMap(function (Carbon $period) use ($progressBar, &$endTime) {
        //         $results = $this->get('/sapi/v1/capital/deposit/hisrec', [
        //             'recvWindow' => 10_000,
        //             'status'     => 1,
        //             'limit'      => 1000,
        //             'startTime'  => $period->getTimestampMs(),
        //             'endTime'    => $endTime->getTimestampMs(),
        //         ]);
        //
        //         $endTime = $period->subMillisecond();
        //
        //         $progressBar->advance();
        //
        //         return $results->json();
        //     })->filter()->tap(function () use ($progressBar) {
        //         $progressBar->finish();
        //         Buzz::moveCursorUp(2)->eraseToEnd();
        //     });
    }

    private function getAsync(Pool $pool, string $url, array $params = []): PromiseInterface|Response
    {
        return $this->get($url, $params, $pool);
    }

    private function get(string $url, array $params = [], null|Pool $pool = null): PromiseInterface|Response
    {
        $params = $params + ['timestamp' => now()->getTimestampMs()];
        $params['signature'] = hash_hmac(
            'sha256',
            http_build_query($params),
            $this->apiSecret
        );

        dd($params);

        return ($pool ?? resolve(Factory::class))
            ->baseUrl('https://api.binance.com')
            ->contentType('application/json')
            ->withHeaders([
                'X-MBX-APIKEY' => $this->apiKey,
            ])
            ->retry(3, 250)
            ->timeout(5)
            ->get($url, $params);
    }

    public function fetchWithdrawalHistory(): Collection
    {
        // "id" => "5bb7070bedd940e3a34b7dca3fd49e9b"
        // "amount" => "279.47"
        // "transactionFee" => "0.25"
        // "coin" => "XRP"
        // "status" => 6
        // "address" => "rJsg5zbUKD1bxU4XWU93Br61pcPpBWdU3B"
        // "addressTag" => ""
        // "txId" => "0428B4353B39BBBC8AF13A17C39B7F2D5590C1FF854CF9B8B012AF10BB16EB6A"
        // "applyTime" => "2018-01-16 13:34:27"
        // "transferType" => 0
        // "info" => ""
        // "confirmNo" => 1
        // "walletType" => 0

        return $this->fetchUsingTimestamps('/sapi/v1/capital/withdraw/history', [
            'status' => 6,
        ]);
    }
}

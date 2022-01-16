<?php

namespace App\Exchanges\Binance;

use App\Services\Buzz\Facade\Buzz;
use ArrayIterator;
use Carbon\CarbonPeriod;
use Clue\React\Mq\Queue;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

class Binance
{
    private Carbon $binanceEpoch;

    private mixed $httpClient;

    public function __construct(private string $apiKey, private string $apiSecret)
    {
        $this->binanceEpoch = Carbon::createFromTimestampMsUTC(1483228800000);
        $this->httpClient = Http::baseUrl('https://api.binance.com')
            ->withHeaders([
                'X-MBX-APIKEY' => $this->apiKey,
            ])
            ->retry(3, 250)
            ->timeout(5);
    }

    /**
     * @see https://binance-docs.github.io/apidocs/spot/en/#deposit-history-supporting-network-user_data
     */
    public function fetchDepositHistory(): Collection
    {
        return $this->fetchUsingTimestamps('/sapi/v1/capital/deposit/hisrec', [
            'status' => 1,
            'limit'  => 10,
        ]);
    }

    private function fetchUsingTimestamps(
        string $url,
        array  $params = [],
    ): Collection {

        Buzz::newLine();
        $progressBar = Buzz::progressBar();

        $results = collect();

        $days = 90;

        $endTime = now();
        $queryCollection = collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days($days))
            ->reverse()
            ->tap(fn($items) => $progressBar->start($items->count()))
            ->map(function (Carbon $period) use ($params, $url, &$endTime) {
                $params = array_merge([
                    'recvWindow' => 60_000,
                    'offset'     => 0,
                    'startTime'  => $period->getTimestampMs(),
                    'endTime'    => $endTime->getTimestampMs(),
                ], $params);
                $endTime = $period->subMillisecond();

                return $params;
            });


        $client = new Browser();

        $client->get('http://www.google.com/')->then(function (ResponseInterface $response) {
            var_dump($response->getHeaders(), (string) $response->getBody());
        });

        // wraps Browser in a Queue object that executes no more than 10 operations at once
        $q = new Queue(10, null, function ($url) use ($client) {
            return $client->get($url);
        });

        foreach ($urls as $url) {
            $q($url)->then(function (ResponseInterface $response) {
                var_dump($response->getHeaders());
            }, function (Exception $e) {
                echo 'Error: '.$e->getMessage().PHP_EOL;
            });
        }

        dd('-o-o-o-o');


        $handler = function ($queryArray, ArrayIterator $workload) use ($results, $url, $progressBar) {
            return $this->getAsync($url, $queryArray)->then(function (Response $response) use (
                $results,
                $workload,
                $progressBar
            ) {
                $progressBar->advance();

                $data = $response->json();
                $resultCount = count($data);

                $results->push(...$data);

                $params = [];
                parse_str($response->effectiveUri()->getQuery(), $params);

                dump($resultCount);
                if ($resultCount === (int) $params['limit']) {
                    dump('might be more txs available');
                    $progressBar->setMaxSteps($progressBar->getMaxSteps() + 1);
                    $params['offset'] = (string) ($params['offset'] + $params['limit']);
                    unset($params['timestamp'], $params['signature']);
                    $workload->append($params);
                }

                return $response->json();
            });
        };

        $pool = dynamic_pool($queryCollection, $handler, 50);

        $responses = $pool->wait();

        dd($results->filter()->count());


        // //
        //
        // Buzz::newLine();
        // $progressBar = Buzz::progressBar();
        //
        // $responses = Http::pool(function (Pool $pool) use ($startTimeKey, $params, $days, $url, $progressBar) {
        //     $endTime = now();
        //
        //     return collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days($days))
        //         ->reverse()
        //         ->tap(function ($items) use ($progressBar) {
        //             $progressBar->start($items->count());
        //         })
        //         ->map(function (Carbon $period) use ($startTimeKey, $params, $url, $pool, $progressBar, &$endTime) {
        //
        //             $request = $this->getAsync($pool, $url, array_merge([
        //                 'recvWindow'  => 60_000,
        //                 'offset'      => 0,
        //                 $startTimeKey => $period->getTimestampMs(),
        //                 'endTime'     => $endTime->getTimestampMs(),
        //             ], $params))->then(function () use ($pool, $progressBar) {
        //                 dd($pool);
        //                 $progressBar->advance();
        //             });
        //
        //             $endTime = $period->subMillisecond();
        //
        //             return $request;
        //         });
        // });
        //
        // dd('---');
        //
        // return collect($responses)->flatMap(function (Response $response) {
        //     return $response->json();
        // })->filter()->tap(function () use ($progressBar) {
        //     $progressBar->finish();
        //     Buzz::moveCursorUp(2)->eraseToEnd();
        // });

        Buzz::newLine();
        $progressBar = Buzz::progressBar();

        $endTime = now();

        return collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days(90))
            ->reverse()
            ->tap(function ($items) use ($progressBar) {
                $progressBar->start($items->count());
            })
            ->flatMap(function (Carbon $period) use ($progressBar, &$endTime) {
                $results = $this->get('/sapi/v1/capital/deposit/hisrec', [
                    'recvWindow' => 10_000,
                    'status'     => 1,
                    'offset'     => 0,
                    'limit'      => 1000,
                    'startTime'  => $period->getTimestampMs(),
                    'endTime'    => $endTime->getTimestampMs(),
                ]);

                dump(count($results->json()));

                $endTime = $period->subMillisecond();

                $progressBar->advance();

                return $results->json();
            })->filter()->tap(function () use ($progressBar) {
                $progressBar->finish();
                Buzz::moveCursorUp(2)->eraseToEnd();
            });
    }

    private function getAsync(string $url, array $params = []): PromiseInterface|Response
    {
        return $this->get($url, $params, async: true);
    }

    private function get(string $url, array $params = [], bool $async = false): PromiseInterface|Response
    {
        return $this->httpClient
            ->async($async)
            ->get($url, $this->buildQueryParams($params));
    }

    private function buildQueryParams(array $params = []): array
    {
        $params = $params + ['timestamp' => now()->getTimestampMs()];
        $params['signature'] = hash_hmac(
            'sha256',
            http_build_query($params),
            $this->apiSecret
        );

        return $params;
    }

    /**
     * @see https://binance-docs.github.io/apidocs/spot/en/#withdraw-history-supporting-network-user_data
     */
    public function fetchWithdrawalHistory(): Collection
    {
        return $this->fetchUsingTimestamps('/sapi/v1/capital/withdraw/history', [
            'status' => 6,
            'limit'  => 1_000,
        ]);
    }

    /**
     * @see https://binance-docs.github.io/apidocs/spot/en/#get-fiat-deposit-withdraw-history-user_data
     */
    public function fetchFiatDepositHistory(): Collection
    {
        return $this->fetchUsingTimestamps('/sapi/v1/fiat/orders', [
            'transactionType' => 0,
            'rows'            => 500,
        ], startTimeKey: 'beginTime');
    }
}

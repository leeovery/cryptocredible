<?php

namespace App\Exchanges\Binance;

use App\Services\Buzz\Facade\Buzz;
use Carbon\CarbonPeriod;
use Clue\React\Mq\Queue;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use Symfony\Component\Console\Helper\ProgressBar;
use function json_decode;

class Binance
{
    private Carbon $binanceEpoch;

    private Queue $queue;

    private Collection $results;

    private Browser $client;

    private ProgressBar $progressBar;

    private LoopInterface $loop;

    public function __construct(private string $apiKey, private string $apiSecret)
    {
        $this->binanceEpoch = Carbon::createFromTimestampMsUTC(1483228800000);
        $this->results = collect();
        $this->loop = Loop::get();

        $this->client = (new Browser(loop: $this->loop))
            ->withBase('https://api.binance.com')
            ->withTimeout(5);
    }

    /**
     * @see https://binance-docs.github.io/apidocs/spot/en/#deposit-history-supporting-network-user_data
     */
    public function fetchDepositHistory(): Collection
    {
        return $this->fetchUsingTimestamps('/sapi/v1/capital/deposit/hisrec', [
            'status' => 1,
            'limit'  => 1_000,
        ]);
    }

    private function fetchUsingTimestamps(string $url, array $params = []): Collection
    {
        Buzz::newLine();
        $days = 90;
        $this->progressBar = Buzz::progressBar();
        $endTime = now();
        $queryCollection = collect(CarbonPeriod::start($this->binanceEpoch)->untilNow()->days($days))
            ->reverse()
            ->tap(fn($items) => $this->progressBar->start($items->count()))
            ->map(function (Carbon $period) use ($params, $url, &$endTime) {
                $params = array_merge([
                    'recvWindow' => 60_000,
                    'offset'     => 0,
                    'startTime'  => $period->getTimestampMs(),
                    'endTime'    => $endTime->getTimestampMs(),
                ], $params);
                $endTime = $period->subMillisecond();

                return $params;
            })->values();

        $this->queue = new Queue(25, null, function (array $queryParams) use ($url) {
            return $this->client->get($url.'?'.http_build_query($this->buildQueryParams($queryParams)), [
                'X-MBX-APIKEY' => $this->apiKey,
            ]);
        });

        foreach ($queryCollection as $queryParams) {
            $this->process($queryParams);
        }

        $this->loop->run();
        $this->progressBar->finish();
        Buzz::moveCursorUp(2)->eraseToEnd();

        return $this->results;
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

    private function process(array $queryParams)
    {
        ($this->queue)($queryParams)->then(function (ResponseInterface $response) use ($queryParams) {
            $this->progressBar->advance();
            $data = json_decode((string) $response->getBody());
            $resultCount = count($data);
            $this->results->push(...$data);

            if ($resultCount === (int) $queryParams['limit']) {
                $this->progressBar->setMaxSteps($this->progressBar->getMaxSteps() + 1);
                $queryParams['offset'] = (string) ($queryParams['offset'] + $queryParams['limit']);
                unset($queryParams['timestamp'], $queryParams['signature']);
                $this->process($queryParams);
            }
        }, function (Exception $e) {
            echo 'Error: '.$e->getMessage().PHP_EOL;
        });
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

<?php

namespace App\Exchanges\CoinbasePro;

use App\Exchanges\CoinbasePro\Exceptions\CoinbaseProException;
use App\Services\Buzz\Facade\Buzz;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoinbasePro
{
    public function __construct(private string $apiKey, private string $apiSecret, private string $apiPassphrase)
    {
    }

    public function fetchWallets(): Collection
    {
        return $this->getAll('/accounts')->mapInto(CoinbaseProWallet::class);
    }

    private function getAll(string $url): Collection
    {
        $limit = 1000;
        $afterCursor = null;
        $collection = collect();

        while (true) {
            $queries = http_build_query([
                'after' => $afterCursor,
                'limit' => $limit,
            ]);

            $response = $this->get("{$url}?{$queries}");

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
            'CB-ACCESS-SIGN'       => base64_encode(
                hash_hmac('sha256', $hash, base64_decode($this->apiSecret), true)
            ),
            'CB-ACCESS-TIMESTAMP'  => $timestamp,
            'CB-ACCESS-PASSPHRASE' => $this->apiPassphrase,
        ];
    }

    public function fetchTransactionsByWallet(Collection $wallets): Collection
    {
        Buzz::newLine();
        $progressBar = Buzz::progressBar($wallets->count(), 'with-message');

        return $wallets->flatMap(function (CoinbaseProWallet $wallet) use ($progressBar) {
            $progressBar->setMessage("{$wallet->currency()} Wallet");

            $transactions = $this->getAll("/accounts/{$wallet->id()}/ledger")
                ->map(fn(array $tx) => tap($tx, fn(&$tx) => $tx['currency'] = $wallet->currency()));

            $progressBar->advance();

            return $transactions;
        })->filter()->tap(function () use ($progressBar) {
            $progressBar->finish();
            $progressBar->clear();
            Buzz::moveCursorUp()->eraseToEnd();
        });
    }
}

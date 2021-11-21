<?php

namespace App\Clients\Coinbase;

use App\SyncingService;
use Illuminate\Support\Facades\Http;

class Coinbase implements SyncingService
{
    public function __construct(private string $baseUrl, private string $apiKey, private string $apiSecret) { }

    public function getAccounts()
    {
        $timestamp = now()->timestamp;
        $hash = sprintf('%s%s%s%s', $timestamp, 'GET', '/v2/accounts', '');

        $accounts = Http::baseUrl($this->baseUrl)
            ->contentType('application/json')
            ->withHeaders([
                'CB-ACCESS-KEY'       => $this->apiKey,
                'CB-ACCESS-SIGN'      => hash_hmac('sha256', $hash, $this->apiSecret),
                'CB-ACCESS-TIMESTAMP' => $timestamp,
            ])
            ->get('/accounts')
            ->json();

        // for every account we need to get multiple items
        // step 1 - get all the account details into collection. need to paginate through items.
        $accountCollection = collect($accounts)->eac();


    }

    public function getTransactions()
    {
        $timestamp = now()->timestamp;
        $hash = sprintf('%s%s%s%s', $timestamp, 'GET', '/v2/accounts', '');

        $accounts = Http::baseUrl($this->baseUrl)
            ->contentType('application/json')
            ->withHeaders([
                'CB-ACCESS-KEY'       => $this->apiKey,
                'CB-ACCESS-SIGN'      => hash_hmac('sha256', $hash, $this->apiSecret),
                'CB-ACCESS-TIMESTAMP' => $timestamp,
            ])
            ->get('/accounts');

        dd($accounts->json());
    }
}

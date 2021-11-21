<?php

namespace App\Providers;

use App\Exchanges\Coinbase\Coinbase;
use Illuminate\Support\ServiceProvider;

class CoinbaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->bind(Coinbase::class, function () {
            $config = config('exchanges.coinbase');

            // Class to pull data from exchange and normalise into format the rest of the code
            // will understand.
            // Coinbase - for connecting to api, pulling data and forming into classes
            // Account
            // Transaction
            // CoinbaseFormatter

            // reduce memory consumption
            // use lazy collection to yield results from the api
            // pull paged accounts, foreach account, get paged transactions
            // work with the transactions and turn into classes

            return new Coinbase(
                baseUrl: $config['base_url'],
                apiKey: $config['api_key'],
                apiSecret: $config['api_secret']
            );
        });
    }
}

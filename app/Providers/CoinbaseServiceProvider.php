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
        $this->app->singleton(Coinbase::class, function () {
            $config = config('exchanges.coinbase');

            return new Coinbase(
                apiKey: $config['api_key'],
                apiSecret: $config['api_secret']
            );
        });

        $this->app->alias(Coinbase::class, 'coinbase');
    }
}

<?php

namespace App\Providers;

use App\Exchanges\CoinbasePro\CoinbasePro;
use Illuminate\Support\ServiceProvider;

class CoinbaseProServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton(CoinbasePro::class, function () {
            $config = config('exchanges.coinbase_pro');

            return new CoinbasePro(
                apiKey: $config['api_key'],
                apiSecret: $config['api_secret'],
                apiPassphrase: $config['api_passphrase']
            );
        });

        $this->app->alias(CoinbasePro::class, 'coinbase-pro');
    }
}

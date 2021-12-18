<?php

namespace App\Providers;

use App\Exchanges\Binance\Binance;
use Illuminate\Support\ServiceProvider;

class BinanceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton(Binance::class, function () {
            $config = config('exchanges.binance');

            return new Binance(
                apiKey: $config['api_key'],
                apiSecret: $config['api_secret']
            );
        });

        $this->app->alias(Binance::class, 'binance');
    }
}

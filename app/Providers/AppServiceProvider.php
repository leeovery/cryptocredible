<?php

namespace App\Providers;

use App\Clients\Coinbase\Coinbase;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // setup coinbase class with client for making api requests
        //

        // process
        // add coinbase credentials to config file
        // run command for syncing cb
        // instantiate client and make requests in batches(?)
        // process rows into db? or just directly into csv file for output
        // output to local dir

        // later can make additional syncers which connect to other APIs.
        // make sycners adhere to an interface
        // normalise data?
        // send to processor
        // send to outputter

        $this->app->bind(Coinbase::class, function () {
            $config = config('syncers.coinbase');

            return new Coinbase(
                baseUrl: $config['base_url'],
                apiKey: $config['api_key'],
                apiSecret: $config['api_secret']
            );
        });

    }
}

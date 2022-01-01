<?php

namespace App\Services\Buzz\Providers;

use App\Services\Buzz\Buzz;
use Illuminate\Support\ServiceProvider;

class BuzzServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Buzz::class);
        $this->app->alias(Buzz::class, 'buzz');
    }
}

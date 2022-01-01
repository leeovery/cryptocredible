<?php

namespace App\Services\Buzz\Facade;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\Buzz\Buzz|\Illuminate\Console\Command
 * @method runTask(string $title, Closure|null $task, string $text)
 */
class Buzz extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'buzz';
    }
}

<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Coinbase;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SyncCoinbase extends Command
{
    protected $signature = 'sync:coinbase';

    protected $description = 'Command description';

    public function handle(Coinbase $coinbase)
    {
        $this->info('Syncing with Coinbase...');

        $coinbase->execute($this);
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

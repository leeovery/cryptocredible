<?php

namespace App;

use Illuminate\Console\Command;

interface SyncingService
{
    public function execute(Command $command);
}

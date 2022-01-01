<?php

namespace App\Commands;

use App\Services\Buzz\Facade\Buzz;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        Buzz::bind($this);

        return parent::run($input, $output);
    }
}

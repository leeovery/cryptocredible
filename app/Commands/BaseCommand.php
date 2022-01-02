<?php

namespace App\Commands;

use App\Services\Buzz\Facade\Buzz;
use Exception;
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

    protected function runTask(string $title = '', $task = null, string $text = '⏳ fetching...')
    {
        $returnValue = null;
        $this->task($title, function () use ($task, &$returnValue) {
            try {
                $returnValue = $task();

                return true;
            } catch (Exception) {
                return false;
            }
        }, $text);

        return $returnValue;
    }
}

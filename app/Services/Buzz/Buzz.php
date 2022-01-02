<?php

namespace App\Services\Buzz;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Buzz
{
    private null|Command $console = null;

    public function bind(Command $console)
    {
        $this->console = $console;
    }

    public function moveCursorUp($lines = 1): static
    {
        $this->output()->write("\x1b[{$lines}A");

        return $this;
    }

    public function output(): OutputStyle|OutputInterface
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getOutput();
    }

    public function eraseToEnd(): static
    {
        $this->output()->writeln("\x1b[0J");

        return $this;
    }

    public function progressBar(int $max = 0, string|null $format = null): ProgressBar
    {
        return tap($this->output()->createProgressBar($max), function (ProgressBar $progressBar) use ($format) {
            $progressBar->setFormatDefinition(
                'with-message',
                "<fg=black;bg=cyan> %message:-41s% </>\n"
                ."%current%/%max% [%bar%] %percent:3s%%\n"
                ." %remaining:-20s%  %memory:20s%"
            );
            if (! is_null($format)) {
                $progressBar->setFormat($format);
            }
            $progressBar->setBarCharacter('<fg=green>=</>');
            $progressBar->setEmptyBarCharacter("<fg=red>•</>");
            $progressBar->setProgressCharacter("<fg=green>➤</>");
            $progressBar->setRedrawFrequency(1);
            $progressBar->maxSecondsBetweenRedraws(0.2);
            $progressBar->minSecondsBetweenRedraws(0.1);
        });
    }

    public function __get($property)
    {
        $console = $this->console ?? new NullConsole;

        return $console->$property;
    }

    public function __call($method, $arguments)
    {
        $console = $this->console ?? new NullConsole;

        return $console->$method(...$arguments);
    }
}

<?php

namespace App\Services\Buzz;

class NullConsole
{
    public function __call($method, $args)
    {
        return $this;
    }
}

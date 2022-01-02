<?php

namespace App\Services\Pool;

use Iterator;

class ExpectingIterator implements Iterator
{
    private bool $wasValid;

    public function __construct(private Iterator $inner)
    {
    }

    public function next()
    {
        if (! $this->wasValid && $this->valid()) {
            // Just do nothing, because the inner iterator became valid
        } else {
            $this->inner->next();
        }

        $this->wasValid = $this->valid();
    }

    public function valid(): bool
    {
        return $this->inner->valid();
    }

    public function current()
    {
        return $this->inner->current();
    }

    public function rewind()
    {
        $this->inner->rewind();

        $this->wasValid = $this->valid();
    }

    public function key()
    {
        return $this->inner->key();
    }
}

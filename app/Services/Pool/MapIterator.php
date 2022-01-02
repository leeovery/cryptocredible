<?php

namespace App\Services\Pool;

use ArrayIterator;
use Iterator;

class MapIterator implements Iterator
{
    public function __construct(private ArrayIterator $inner, private $handler)
    {
    }

    public function next()
    {
        // Cleanup current (processed) entry. We cannot unset completely, unfortunately, because then indexing will be
        // broken (and the whole execution will be broken).
        $this->valid() && $this->inner->offsetSet($this->inner->key(), null);

        $this->inner->next();
    }

    public function valid(): bool
    {
        return $this->inner->valid();
    }

    public function current()
    {
        return call_user_func($this->handler, $this->inner->current(), $this->inner);
    }

    public function rewind()
    {
        $this->inner->rewind();
    }

    public function key()
    {
        return $this->inner->key();
    }
}

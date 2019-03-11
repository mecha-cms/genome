<?php

class Range extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate {

    protected $chunk;
    protected $current;
    protected $lot;

    public function __construct($in, int $chunk, int $current) {
        $this->lot = $in;
        $this->chunk = $chunk;
        $this->current = $current;
    }

    public function count() {
        return count($this->lot);
    }

    public function next() {}
    public function prev() {}

}
<?php namespace Pager;

class Pages extends \Pager {

    public function __construct(array $data, $chunk = [5, 0], string $parent) {
        $data = \array_chunk($data, $chunk[0]);
        $i = $chunk[1];
        $this->next = isset($data[$i + 1]) ? $parent . '/' . ($i + 2) : null;
        $this->parent = $parent !== $GLOBALS['URL']['$'] ? $parent : null;
        $this->prev = isset($data[$i - 1]) ? $parent . '/' . $i : null;
    }

}
<?php namespace Pager;

class Page extends \Pager {

    public function __construct(array $data, string $current, string $parent) {
        $data = \array_values($data);
        $count = \count($data);
        if (false !== ($i = \array_search($current, $data, true))) {
            $this->next = $i + 1 < $count ? \rtrim($parent . '/' . $data[$i + 1], '/') : null;
            $this->prev = $i - 1 > -1 ? \rtrim($parent . '/' . $data[$i - 1], '/') : null;
        }
        $this->parent = $parent !== $GLOBALS['URL']['$'] ? $parent : null;
    }

}
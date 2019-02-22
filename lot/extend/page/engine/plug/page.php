<?php namespace fn\page;

function time(...$lot) {
    $n = $this['slug'];
    // Set `time` value from the page’s file name
    if (
        \is_string($n) && (
            // `2017-04-21.page`
            \substr_count($n, '-') === 2 ||
            // `2017-04-21-14-25-00.page`
            \substr_count($n, '-') === 5
        ) &&
        \is_numeric(\str_replace('-', "", $n)) &&
        \preg_match('#^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])(-([0-1]\d|2[0-4])(-([0-5]\d|60)){2})?$#', $n)
    ) {
        $date = new \Date($n);
    // Else…
    } else {
        $date = new \Date($this['time']);
    }
    return $lot ? $date(...$lot) : $date;
}

function update(...$lot) {
    $date = new \Date($this['update']);
    return $lot ? $date(...$lot) : $date;
}

\Page::_('time', __NAMESPACE__ . "\\time");
\Page::_('update', __NAMESPACE__ . "\\update");

\Page::$data = \Config::get('page', true);
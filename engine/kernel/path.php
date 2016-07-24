<?php

class Path extends __ {

    public function url($path) {
        $url = str_replace([ROOT, DS, '\\'], [URL::url(), '/', '/'], $path);
        // Fix broken external URL `http://://example.com`, `http:////example.com`
        $url = str_replace(['://://', ':////'], '://', $url);
        // @ditto `http:example.com`
        if (strpos($url, URL::scheme() . ':') === 0 && strpos($url, URL::protocol()) !== 0) {
            $url = str_replace(X . URL::scheme() . ':', URL::protocol(), X . $url);
        }
        return $path;
    }

    public function D($path) {
        return dirname($path);
    }

    public function B($path) {
        return basename($path);
    }

}
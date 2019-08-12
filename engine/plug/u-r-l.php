<?php

URL::_('long', function(string $path, $root = true) {
    global $url;
    // `URL::long('//example.com')`
    if (strpos($path, '//') === 0) {
        return rtrim($url->scheme . ':' . $path, '/');
    // `URL::long('/foo/bar/baz/qux')`
    } else if (strpos($path, '/') === 0) {
        return rtrim($url->ground . $path, '/');
    }
    // `URL::long('&foo=bar&baz=qux')`
    $a = explode('?', $path, 2);
    if (count($a) === 1 && strpos($a[0], '&') !== false) {
        $a = explode('&', strtr($a[0], ['&amp;' => '&']), 2);
        $path = implode('?', $a);
    }
    if (
        strpos($path, '://') === false &&
        strpos($path, 'data:') !== 0 &&
        strpos($path, 'javascript:') !== 0 &&
        strpos($path, '?') !== 0 &&
        strpos($path, '&') !== 0 &&
        strpos($path, '#') !== 0
    ) {
        return rtrim($url->{$root ? 'ground' : 'root'} . '/' . ltrim($path, '/'), '/');
    }
    return $path;
});

URL::_('short', function(string $path, $root = true) {
    global $url;
    if (strpos($path, '//') === 0 && strpos($path, '//' . $url->host) !== 0) {
        return $path; // Ignore external URL
    }
    return $root ? str_replace([
        // `http://127.0.0.1`
        P . $url->ground,
        // `//127.0.0.1`
        P . '//' . $url->host,
        P
    ], "", P . $path) : ltrim(str_replace([
        // `http://127.0.0.1/foo`
        P . $url->root,
        // `//127.0.0.1/foo`
        P . '//' . $url->host . $url->directory
    ], "", P . $path), '/');
});
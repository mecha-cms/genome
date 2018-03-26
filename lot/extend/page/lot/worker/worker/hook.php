<?php

function fn_document_if($content) {
    if (strpos($content, '<html ') !== false) {
        return preg_replace_callback('#<html(?:\s[^<>]*?)?>#', function($m) {
            if (
                strpos($m[0], ' class="') !== false ||
                strpos($m[0], ' class ') !== false ||
                substr($m[0], -7) === ' class>'
            ) {
                $a = HTML::apart($m[0]);
                if (isset($a[2]['class[]'])) {
                    $c = [];
                    foreach (array_filter((array) Config::get('is', [])) as $k => $v) {
                        $c[] = 'is-' . $k;
                    }
                    foreach (array_filter((array) Config::get('has', [])) as $k => $v) {
                        $c[] = 'has-' . $k;
                    }
                    if ($x = Config::get('is.error')) {
                        $c[] = 'error-' . $x;
                    }
                    $c = array_unique(array_merge($a[2]['class[]'], $c));
                    sort($c);
                    $a[2]['class[]'] = $c;
                }
                return call_user_func_array('HTML::unite', $a);
            }
            return $m[0];
        }, $content);
    }
    return $content;
}

Hook::set('shield.yield', 'fn_document_if', 0);
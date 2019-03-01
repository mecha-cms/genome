<?php

Hook::set('content', function($content) {
    if (strpos($content, '<input ') !== false) {
        $content = preg_replace_callback('#<input(?:\s[^>]*)?>#', function($m) {
            $e = new HTML($m[0]);
            if (!$n = $e['name']) {
                return $m[0];
            }
            $t = $e['type'];
            // Convert `foo[bar][baz]` to `foo.bar.baz`
            $n = 'form.' . str_replace(['.', '[', ']', X], [X, '.', "", "\\."], $n);
            $v = Session::get($n);
            if ($t === 'checkbox' || $t === 'radio') {
                $e['checked'] = isset($v) && $v === $e['value'];
            } else {
                $e['value'] = $v ?? $e['value'];
            }
            Session::reset($n);
            return $e;
        }, $content);
    }
    if (strpos($content, '<select ') !== false) {
        $content = preg_replace_callback('#<select(?:\s[^>]*)?>[\s\S]*?</select>#', function($m) {
            $e = new HTML($m[0]);
            if (!$n = $e['name']) {
                return $m[0];
            }
            // Convert `foo[bar][baz]` to `foo.bar.baz`
            $n = 'form.' . str_replace(['.', '[', ']', X], [X, '.', "", "\\."], $n);
            $v = Session::get($n);
            $e[1] = preg_replace_callback('#<option(?:\s[^>]*)?>[\s\S]*?</option>#', function($m) use($n, $v) {
                $e = new HTML($m[0]);
                $e['selected'] = isset($v) && $v === ($e['value'] ?? $e[1]);
                return $e;
            }, $e[1]);
            return $e;
        }, $content);
    }
    if (strpos($content, '<textarea ') !== false) {
        $content = preg_replace_callback('#<textarea(?:\s[^>]*)?>[\s\S]*?</textarea>#', function($m) {
            $e = new HTML($m[0]);
            if (!$n = $e['name']) {
                return $m[0];
            }
            // Convert `foo[bar][baz]` to `foo.bar.baz`
            $n = 'form.' . str_replace(['.', '[', ']', X], [X, '.', "", "\\."], $n);
            $v = Session::get($n, false);
            $e[1] = is_string($v) ? htmlspecialchars($v) : $e[1];
            Session::reset($n);
            return $e;
        }, $content);
    }
    return $content;
}, 0);
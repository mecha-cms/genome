<?php

namespace _\shield {
    function message($content) {
        if (\strpos($content, '</message>') !== false) {
            return \preg_replace_callback('#(?:\s*<message(?:\s[^>]+)?>[\s\S]*?<\/message>\s*)+#', function($m) {
                return '<div class="messages p">' . \str_replace([
                    '<message type="',
                    '</message>'
                ], [
                    '<p class="message message-',
                    '</p>'
                ], $m[0]) . '</div>';
            }, $content);
        }
        return $content;
    }
    \Hook::set('content', __NAMESPACE__, 0);
    \Hook::set('content', __NAMESPACE__ . "\\message", 0);
}

namespace _ {
    // Generate HTML class(es)
    function shield($content) {
        $r = 'html';
        if (\strpos($content, '<' . $r . ' ') !== false) {
            return \preg_replace_callback('#<' . \x($r) . '(?:\s[^>]*)?>#', function($m) use($r) {
                if (
                    \strpos($m[0], ' class="') !== false ||
                    \strpos($m[0], ' class ') !== false ||
                    \substr($m[0], -7) === ' class>'
                ) {
                    $root = new \HTML($m[0]);
                    $c = $root['class'] === true ? [] : \preg_split('#\s+#', $root['class'] ?? "");
                    foreach (['has', 'is', 'not'] as $key) {
                        foreach (\array_filter((array) \Config::get($key)) as $k => $v) {
                            $c[] = $key . '-' . $k;
                        }
                    }
                    if ($x = \Config::get('is.error')) {
                        $c[] = 'error-' . $x;
                    }
                    $c = \array_unique($c);
                    \sort($c);
                    $root['class'] = \trim(\implode(' ', $c));
                    return $root;
                }
                return $m[0];
            }, $content);
        }
        return $content;
    }
}
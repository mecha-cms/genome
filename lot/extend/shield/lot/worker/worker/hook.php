<?php namespace fn\shield;

// Generate HTML class(es) based on current page conditional statement(s)
function root($content) {
    $r = \Shield::$config['root'][0];
    if (\strpos($content, '<' . $r . ' ') !== false) {
        return \preg_replace_callback('#<' . \x($r) . '(?:\s[^>]*)?>#', function($m) use($r) {
            if (
                \strpos($m[0], ' class="') !== false ||
                \strpos($m[0], ' class ') !== false ||
                \substr($m[0], -7) === ' class>'
            ) {
                $root = new \HTML($m[0]);
                $c = preg_split('#\s+#', $root['class'] ?? "");
                foreach (['has', 'is', 'not'] as $key) {
                    foreach (\array_filter((array) \Config::get($key)) as $k => $v) {
                        $c[] = $key . '-' . $k;
                    }
                }
                if ($x = \Config::get('is.error')) {
                    $c[] = 'error-' . $x;
                }
                $c = array_unique($c);
                sort($c);
                $root['class'] = trim(implode(' ', $c));
                return $root;
            }
            return $m[0];
        }, $content);
    }
    return $content;
}

\Hook::set('content', __NAMESPACE__ . "\\root", 0);
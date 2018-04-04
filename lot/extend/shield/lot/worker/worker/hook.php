<?php

// Store default shield folder state to registry…
if ($name = Extend::state('shield', 'name')) {
    // Prioritize default state
    Config::alt(['shield' => $name]);
}

// Generate relative shield path to the `.\lot\shield\*` folder
function fn_shield_path($path) {
    if (!$path) {
        return $path;
    }
    global $config;
    $o = [];
    foreach ($path = (array) $path as $v) {
        $o[] = SHIELD . DS . $config->shield . DS . str_replace(ROOT . DS, "", $v);
    }
    return array_merge($o, $path);
}

// Generate HTML class(es) based on current page conditional statement(s)
function fn_shield_yield($content) {
    $if = Extend::state('shield', 'if');
    if (strpos($content, '<' . $if[0] . ' ') !== false) {
        return preg_replace_callback('#<' . x($if[0]) . '(?:\s[^<>]*?)?>#', function($m) use($if) {
            if (
                strpos($m[0], ' class="') !== false ||
                strpos($m[0], ' class ') !== false ||
                substr($m[0], -7) === ' class>'
            ) {
                $a = HTML::apart($m[0]);
                if (isset($a[2]['class[]'])) {
                    $c = [];
                    foreach (array_filter((array) Config::get('has', [])) as $k => $v) {
                        $c[] = 'has-' . $k;
                    }
                    foreach (array_filter((array) Config::get('is', [])) as $k => $v) {
                        $c[] = 'is-' . $k;
                    }
                    foreach (array_filter((array) Config::get('not', [])) as $k => $v) {
                        $c[] = 'not-' . $k;
                    }
                    if ($x = Config::get('is.error')) {
                        $c[] = 'error-' . $x;
                    }
                    $c = array_unique(array_merge($a[2]['class[]'], $c));
                    sort($c);
                    $a[2]['class[]'] = $c;
                }
                return HTML::unite($a);
            }
            return $m[0];
        }, $content);
    }
    return $content;
}

Hook::set('shield.path', 'fn_shield_path', 0);
Hook::set('shield.yield', 'fn_shield_yield', 0);
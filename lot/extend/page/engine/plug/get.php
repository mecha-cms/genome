<?php

// Hard-coded data key(s) which the value must be standardized: `time`, `slug`
function _fn_get_data($v, $n = null) {
    $n = $n ?: Path::N($v);
    $v = file_get_contents($v);
    if ($n === 'time' || $n === 'update') {
        $v = (new Date($v))->format(DATE_WISE);
    } else if ($n === 'slug') {
        $v = h($v);
    }
    return $v;
}

function _fn_get_page($path, $key = null, $fail = false, $for = null) {
    if (!file_exists($path)) {
        return $fail;
    }
    $output = Page::open($path)->get([
        $for => null,
        'path' => $path,
        'time' => null,
        'update' => null,
        'slug' => null,
        'state' => null
    ]);
    $data = Path::F($path);
    if (is_dir($data)) {
        if ($for === null) {
            foreach (g($data, 'data') as $v) {
                $n = Path::N($v);
                $output[$n] = e(_fn_get_data($v, $n));
            }
        } else if ($v = File::exist($data . DS . $for . '.data')) {
            $output[$for] = e(_fn_get_data($v, $for));
        }
    }
    return !isset($key) ? $output : (array_key_exists($key, $output) ? $output[$key] : $fail);
}

function fn_get_pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $output = [];
    $by = is_array($sort) && isset($sort[1]) ? $sort[1] : null;
    if ($input = g($folder, $state)) {
        foreach ($input as $v) {
            if (Path::N($v) === '$') continue;
            $output[] = _fn_get_page($v, null, false, $by);
        }
        $output = $o = Anemon::eat($output)->sort($sort)->vomit();
        if (isset($key)) {
            $o = [];
            foreach ($output as $v) {
                if (!array_key_exists($key, $v)) {
                    continue;
                }
                $o[] = $v[$key];
            }
        }
        unset($output);
        return !empty($o) ? $o : false;
    }
    return false;
}

Get::_('pages', 'fn_get_pages');
<?php namespace fn\get;

// Hard-coded data key(s) which the value must be standardized: `time`, `slug`
function _data($v, $n = null) {
    $n = $n ?: \Path::N($v);
    $v = file_get_contents($v);
    if ($n === 'time' || $n === 'update') {
        $v = (new \Date($v))->format(DATE_WISE);
    } else if ($n === 'slug') {
        $v = \h($v);
    }
    return $v;
}

function _page($path, $key = null, $fail = false, $for = null) {
    if (!file_exists($path)) {
        return $fail;
    }
    $out = \Page::open($path)->get([
        $for => null,
        'path' => $path,
        'time' => null,
        'update' => null,
        'slug' => null,
        'state' => null
    ]);
    $data = \Path::F($path);
    if (is_dir($data)) {
        if ($for === null) {
            foreach (\g($data, 'data') as $v) {
                $n = \Path::N($v);
                $out[$n] = \e(_data($v, $n));
            }
        } else if ($v = \File::exist($data . DS . $for . '.data')) {
            $out[$for] = \e(_data($v, $for));
        }
    }
    return !isset($key) ? $out : (array_key_exists($key, $out) ? $out[$key] : $fail);
}

function pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $out = [];
    $by = is_array($sort) && isset($sort[1]) ? $sort[1] : null;
    if ($in = \g($folder, $state)) {
        foreach ($in as $v) {
            if (\Path::N($v) === '$') continue;
            $out[] = _page($v, null, false, $by);
        }
        $out = $o = \Anemon::eat($out)->sort($sort)->vomit();
        if (isset($key)) {
            $o = [];
            foreach ($out as $v) {
                if (!array_key_exists($key, $v)) {
                    continue;
                }
                $o[] = $v[$key];
            }
        }
        unset($out);
        return !empty($o) ? $o : false;
    }
    return false;
}

\Get::_('pages', __NAMESPACE__ . '\pages');
<?php

class Get extends Genome {

    public static function page($path, $key = null, $fail = false) {
        if (!file_exists($path)) return false;
        global $config;
        $i = Page::$i;
        $o = a($config->page);
        $slug = Path::N($path);
        $time = date(DATE_WISE, File::T($path));
        $state = Path::X($path);
        $output = [
            'path' => $path,
            'url' => To::url($path),
            'time' => $time,
            'update' => $time,
            'kind' => [0],
            'slug' => $slug,
            'state' => $state
        ];
        $output = Anemon::extend($o, $output);
        $fields = Path::D($path) . DS . Path::N($path);
        if (is_dir($fields)) {
            foreach (g($fields, '{' . implode(',', $i) . '}.data', "", false) as $v) {
                $n = Path::N($v);
                $v = file_get_contents($v);
                if ($n === $i[0]) {
                    $v = (new Date($v))->format();
                } else if ($n === $i[1] && strpos($v, '[') === false) {
                    $v = e(explode(',', $v));
                } else if ($n === $i[3]) {
                    $v = isset(Page::$states[$v]) ? Page::$states[$v] : $v;
                }
                $output[$n] = e($v);
            }
        }
        $output['id'] = (string) date('U', strtotime($output['time']));
        return !isset($key) ? $output : (array_key_exists($key, $output) ? $output[$key] : $fail);
    }

    public static function pages($folder = PAGE, $state = [], $sort = 1, $by = 'time', $key = null) {
        $states = Page::$states;
        $state = empty($state) ? $states : (array) $state;
        $state = implode(',', $state === [true] ? array_slice($states, 1) : $state);
        $output = [];
        if ($input = g($folder, $state, "", false)) {
            foreach ($input as $v) {
                $output[] = self::page($v);
            }
            $output = $oo = Anemon::eat($output)->sort($sort, $by)->vomit();
            if (isset($key)) {
                $oo = [];
                foreach ($output as $o) {
                    if (!array_key_exists($key, $o)) continue;
                    $oo[] = $o[$key];
                }
            }
            return !empty($oo) ? $oo : false;
        }
        return false;
    }

}
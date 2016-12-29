<?php

class Get extends Genome {

    public static function page($path, $key = null, $fail = false) {
        if (!file_exists($path)) return false;
        $i = Page::$i;
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
        $fields = Path::D($path) . DS . Path::N($path);
        if (is_dir($fields)) {
            foreach (g($fields, '{' . implode(',', $i) . '}.data', "", false) as $v) {
                $n = Path::N($v);
                $v = file_get_contents($v);
                if ($n === $i[0]) {
                    $v = (new Date($v))->format();
                } else if ($n === $i[1]) {
                    $v = e(explode(',', $v));
                } else if ($n === $i[3]) {
                    $v = isset(Page::$states[$v]) ? Page::$states[$v] : $v;
                }
                $output[$n] = $v;
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
                $output[] = self::page($v, $key);
            }
            return !empty($output) ? Anemon::eat($output)->sort(1, !isset($key) ? $by : null)->vomit() : false;
        }
        return false;
    }

}
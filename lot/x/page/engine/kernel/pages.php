<?php

class Pages extends Files {

    // Inherit to `Files::file()`
    public function file(string $path): \ArrayAccess {
        return $this->page($path);
    }

    public function page(string $path) {
        return new Page($path);
    }

    // Inherit to `Files::from()`
    public static function from(...$lot) {
        $folder = array_shift($lot);
        $x = array_shift($lot) ?? 'page';
        $deep = array_shift($lot) ?? 0;
        $pages = [];
        foreach (g($folder, $x, $deep) as $k => $v) {
            if (pathinfo($k, PATHINFO_FILENAME) === "") {
                continue;
            }
            $pages[] = $k;
        }
        return new static($pages);
    }

}
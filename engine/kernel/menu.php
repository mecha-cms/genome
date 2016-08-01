<?php

class Menu extends __ {

    protected static $lot = [];

    public static $config = [
        'classes' => [
            'parent' => 'parent',
            'child' => 'child child-%d',
            'current' => 'current',
            'separator' => 'separator'
        ]
    ];

    // Set
    public static function set($id, $menus = []) {
        $c = static::class;
        if (!isset(self::$lot[0][$c][$id])) {
            self::$lot[1][$c][$id] = $menus;
        }
    }

    // Get
    public function get($id = null, $fail = false) {
        $c = static::class;
        if ($id !== null) {
            return self::$lot[1][$c][$id] ?? $fail;
        }
        return !empty(self::$lot[1][$c]) ? self::$lot[1][$c] : $fail;
    }

    // Reset
    public static function reset($id = null) {
        $c = static::class;
        self::$lot[0][$c][$id] = self::$lot[1][$c][$id] ?? 1;
        if ($id !== null) {
            unset(self::$lot[1][$c][$id]);
        } else {
            self::$lot[1][$c] = [];
        }
    }

    // Render as HTML
    public static function __callStatic($id, $lot = []) {
        $c = static::class;
        $s = self::$config['classes'];
        $tree = new Tree([
            'trunk' => $type,
            'branch' => $type,
            'twig' => 'li',
            'classes' => [
                'trunk' => $s['parent'],
                'branch' => $s['child'],
                'twig' => false,
                'current' => $s['current'],
                'chink' => $s['separator']
            ]
        ]);
        if (!isset(self::$lot[1][$c][$id])) {
            return false;
        }
        $AD = ['ul', "", $id . ':'];
        $lot = Anemon::extend($AD, $lot);
        $type = $lot[0];
        $lot[0] = Hook::NS('menu:input', [$id], self::$lot[1][$c][$id]);
        if (!is_array($lot[0])) return "";
        return Hook::NS('menu:output', [$id], $tree->grow($lot));
    }

}
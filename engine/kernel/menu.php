<?php

class Menu extends DNA {

    protected $lot = [];

    public $config = [
        'classes' => [
            'parent' => 'parent',
            'child' => 'child child-%d',
            'current' => 'current',
            'separator' => 'separator'
        ]
    ];

    // Add
    public function add($id, $menus = []) {
        $c = static::class;
        if (!isset($this->lot[0][$c][$id])) {
            $this->lot[1][$c][$id] = $menus;
        }
    }

    // Remove
    public function remove($id = null) {
        $c = static::class;
        $this->lot[0][$c][$id] = $this->lot[1][$c][$id] ?? 1;
        if ($id !== null) {
            unset($this->lot[1][$c][$id]);
        } else {
            $this->lot[1][$c] = [];
        }
    }

    // Check
    public function exist($id = null, $fail = false) {
        $c = static::class;
        if ($id !== null) {
            return $this->lot[1][$c][$id] ?? $fail;
        }
        return !empty($this->lot[1][$c]) ? $this->lot[1][$c] : $fail;
    }

    // Render as HTML
    public function __callStatic($id, $lot = []) {
        $c = static::class;
        $tree = new Tree();
        $s = $this->config['classes'];
        $tree->config = [
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
        ];
        if (!isset($this->lot[1][$c][$id])) {
            return false;
        }
        $AD = ['ul', "", $id . ':'];
        $lot = Anemon::extend($AD, $lot);
        $type = $lot[0];
        $lot[0] = Filter::NS('menu:input', $this->lot[1][$c][$id], [$id]);
        if (!is_array($lot[0])) return "";
        return Filter::NS('menu:output', $tree->grow($lot), [$id]);
    }

}
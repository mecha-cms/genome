<?php

class Tree extends Socket {

    public $config = [
        'trunk' => 'ul',
        'branch' => 'ul',
        'twig' => 'li',
        'classes' => [
            'trunk' => 'trunk',
            'branch' => 'branch branch-%d',
            'twig' => 'twig',
            'current' => 'current',
            'chink' => 'chink'
        ]
    ];

    public function __construct($config = []) {
        Anemon::extend($this->config, $config);
        return $this;
    }

    protected function _grow($tree, $dent = "", $NS = "", $i = 0) {
        $c_u = URL::long();
        $c_u_c = URL::current();
        $c_c = $this->config;
        $c_c_c = $c_c['classes'];
        $cell = $dent . str_repeat(I, $i) . '<' . $c_c[$i === 0 ? 'trunk' : 'branch'] . ($i === 0 ? ($c_c_c['trunk'] !== false ? ' class="' . $c_c_c['trunk'] . '"' : "") : ($c_c_c['branch'] !== false ? ' class="' . sprintf($c_c_c['branch'], $i / 2) . '"' : "")) . '>' . N;
        foreach ($tree as $k => $v) {
            if (!Is::anemon($v)) {
                $url = URL::long($v);
                $hole = $v === false ? ' ' . $c_c_c['chink'] : "";
                $current = $url === $c_u_c || ($url !== $c_u && strpos($c_u_c . '/', $url . '/') === 0) ? ' ' . $c_c_c['current'] : "";
                $c = trim(($c_c_c['twig'] !== false ? $c_c_c['twig'] : "") . $hole . $current);
                $twig = '<' . $c_c['twig'] . ($c ? ' class="' . $c . '"' : "") . '>';
                if ($v !== false) {
                    // List item w/o anchor: `['foo']`
                    if (is_int($k)) {
                        $twig .= Hook::NS($NS . 'anchor', [], '<span class="a" tabindex="0">' . $v . '</span>');
                    // List item w/o anchor: `['foo' => null]`
                    } elseif ($v === null) {
                        $twig .= Hook::NS($NS . 'anchor', [], '<span class="a" tabindex="0">' . $k . '</span>');
                    // List item w/ anchor: `['foo' => '/']`
                    } else {
                        $url = Hook::NS($NS . 'url', [], $url);
                        $twig .= Hook::NS($NS . 'anchor', [], '<a href="' . $url . '">' . $k . '</a>');
                    }
                }
                $s = explode(' ', $c_c['twig']);
                $s = $s[0];
                $cell .= $dent . str_repeat(I, $i + 1) . Hook::NS($NS . 'twig', [$i + 1], $twig . '</' . $s . '>') . N;
            } else {
                // `text: path/to/url`
                if (preg_match('#^\s*(.*?)\s*\:\s*(.*?)\s*$#', $k, $m)) {
                    $_k = $m[1];
                    $_v = trim($m[2]) !== "" ? URL::long($m[2]) : '#';
                } else {
                    $_k = $k;
                    $_v = null;
                }
                $url = Hook::NS($NS . 'url', $_v);
                $s = explode(' ', $c_c['branch']);
                $s = ' ' . $s[0];
                $current = $url === $c_u_c || ($url !== $c_u && strpos($c_u_c . '/', $url . '/') === 0) ? ' ' . $c_c_c['current'] : "";
                $c = trim(($c_c_c['twig'] !== false ? $c_c_c['twig'] : "") . $current . $s);
                $twig = '<' . $c_c['twig'] . ($c ? ' class="' . $c . '"' : "") . '>';
                $twig .= N . $dent . str_repeat(I, $i + 2);
                $twig .= Hook::NS($NS . 'anchor', [], $_v !== null ? '<a href="' . $url . '">' . $_k . '</a>' : '<span class="a" tabindex="0">' . $_k . '</span>');
                $twig .= N . $this->_grow($v, $dent, $NS, $i + 2);
                $twig .= $dent . str_repeat(I, $i + 1);
                $s = explode(' ', $c_c['twig']);
                $s = $s[0];
                $cell .= $dent . str_repeat(I, $i + 1) . Hook::NS($NS . 'twig', [$i + 1], $twig . '</' . $s . '>') . N;
            }
        }
        $s = explode(' ', $c_c[$i === 0 ? 'trunk' : 'branch']);
        $s = $s[0];
        return Hook::NS($NS . 'branch', [$i], rtrim($cell, N) . (!empty($tree) ? N . $dent . str_repeat(I, $i) : "") . '</' . $s . '>') . N;
    }

    public function grow($tree = null, $dent = "", $NS = 'tree:') {
        return CELL_BEGIN . Hook::NS($NS . 'trunk', [], rtrim($this->_grow($tree, $dent, $NS, 0), N)) . CELL_END;
    }

}
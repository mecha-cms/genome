<?php

abstract class Pager extends Genome {

    // <https://commons.wikimedia.org/wiki/Arrow_symbol>
    // <https://commons.wikimedia.org/wiki/File:Ski_trail_rating_symbol-black_diamond.svg>
    const next = '<svg height="16" viewBox="0 0 460.5 531.74" width="16"><path d="M 0.5,0.866 459.5,265.87 0.5,530.874 z" fill="currentColor"></path></svg>';
    const parent = '<svg height="16" viewBox="0 0 599 599" width="16"><path d="M 300,575 L 575,300 L 300,25 L 25,300 L 300,575 z" fill="currentColor"></path></svg>';
    const prev = '<svg height="16" viewBox="0 0 460.5 531.74" width="16"><path d="M 460,530.874 1,265.87 460,0.866 z" fill="currentColor"></path></svg>';

    public $next;
    public $parent;
    public $prev;

    public function __toString() {
        return $this->prev(self::prev) . ' ' . $this->parent(self::parent) . ' ' . $this->next(self::next);
    }

    public function next(string $text = null) {
        $next = isset($this->next) ? $this->next . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $next !== null ? '<a href="' . $next . '" rel="next">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $next;
    }

    public function parent(string $text = null) {
        $parent = isset($this->parent) ? $this->parent . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $parent !== null ? '<a href="' . $parent . '">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $parent;
    }

    public function prev(string $text = null) {
        $prev = isset($this->prev) ? $this->prev . strtr($GLOBALS['URL']['query'], ['&' => '&amp;']) . $GLOBALS['URL']['hash'] : null;
        if (isset($text)) {
            return $prev !== null ? '<a href="' . $prev . '" rel="prev">' . $text . '</a>' : '<span>' . $text . '</span>';
        }
        return $prev;
    }

}
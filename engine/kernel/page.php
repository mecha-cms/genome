<?php

/**
 * =============================================================
 *  PAGE
 * =============================================================
 *
 * -- CODE: ----------------------------------------------------
 *
 *    echo Page::header(array(
 *        'Title' => 'Test Page',
 *        'Content Type' => 'HTML'
 *    ))->content('<p>Test!</p>')->put();
 *
 * -------------------------------------------------------------
 *
 */

class Page extends DNA {

    public $open = null;
    public $header = [];
    public $content = [];

    protected $i = 0;

    // Encode page separator
    public function x($x) {
        $a = trim(S_I);
        $b = trim(S_B);
        return str_replace([$a, $b], [To::html_dec($a), To::html_dec($b)], $x);
    }

    // Decode page separator
    public function v($x) {
        $a = trim(S_I);
        $b = trim(S_B);
        return str_replace([To::html_dec($a), To::html_dec($b)], [$a, $b], $x);
    }

    // Create the page
    protected function _create() {
        $header = "";
        $S = N . S_B . N;
        foreach ($this->header as $k => $v) {
            $header .= $k . S_I . $v . N;
        }
        $content = implode($S, $this->content);
        return trim(substr($header, 0, -1) . $S . $content);
    }

    // Create from text
    public function text($text, $content = 'content', $NS = 'page:', $origin = [], $lot = []) {
        $c = $content !== false ? $content : 'content';
        $S = S_B;
        foreach ($origin as $k => $v) {
            $origin[$k . '_raw'] = Filter::NS($NS . $k . '_raw', $v, $lot);
            $origin[$k] = Filter::NS($NS . $k, $v, $lot);
        }
        if (!$content) {
            // By file path
            if (strpos($text, ROOT) === 0 && ($text = File::open($text)->get(trim($S))) !== false) {
                $text = Filter::NS($NS . 'input', n($text), $NS, $lot);
                Anemon::extend($origin, $this->_header($text, $NS, $lot));
            // By file content
            } else {
                $text = Filter::NS($NS . 'input', n($text), $NS, $lot);
                if (strpos($text, $_) !== false) {
                    $s = explode($S, $text, 2);
                    Anemon::extend($origin, $this->_header(trim($s[0]), $NS, $lot));
                    if (isset($s[1]) && !Is::void($s[1])) {
                        $origin[$c . '_raw'] = trim($s[1]);
                    }
                }
            }
        } else {
            // By file path
            if (strpos($text, ROOT) === 0 && file_exists($text)) {
                $text = file_get_contents($text);
            }
            $text = Filter::NS($NS . 'input', n($text), $NS, $lot);
            // By file content
            if ($text === $S || strpos($text, $S) === false) {
                $origin[$c . '_raw'] = $this->x(trim($text));
            } else {
                $s = explode($S, $text, 2);
                Anemon::extend($origin, $this->_header(trim($s[0]), $NS, $lot));
                if (isset($s[1]) && !Is::void($s[1])) {
                    $origin[$c . '_raw'] = trim($s[1]);
                }
            }
        }
        unset($origin['__'], $origin['___raw']);
        Anemon::extend($lot, $origin);
        if (isset($origin[$c . '_raw'])) {
            $content_x = explode($S, $origin[$c . '_raw']);
            if (count($content_x)) {
                $origin[$c . '_raw'] = $origin[$c] = [];
                $i = 0;
                foreach ($content_x as $v) {
                    $v = $this->v(trim($v));
                    $v = Filter::NS($NS . $c . '_raw', $v, $lot, $i + 1);
                    $origin[$c . '_raw'][$i] = $v;
                    $v = Filter::NS($NS . 'iota.input', $v, $lot, $i + 1);
                    $v = Filter::NS($NS . $c, $v, $lot, $i + 1);
                    $v = Filter::NS($NS . 'iota.output', $v, $lot, $i + 1);
                    $origin[$c][$i] = $v;
                    $i++;
                }
            } else {
                $v = $this->v($origin[$c . '_raw']);
                $v = Filter::NS($NS . $c . '_raw', $v, $lot, 1);
                $origin[$c . '_raw'] = $v;
                $v = Filter::NS($NS . 'iota.input', $v, $lot, 1);
                $v = Filter::NS($NS . $c, $v, $lot, 1);
                $v = Filter::NS($NS . 'iota.output', $v, $lot, 1);
                $origin[$c] = $v;
            }
        }
        return Filter::NS($NS . 'output', $origin, $NS, $lot);
    }

    protected static function _header($text, $NS, $lot) {
        $output = [];
        $s = explode(N, trim($text));
        foreach ($s as $v) {
            $f = explode(S_I, $v, 2);
            if (!isset($f[1])) $f[1] = false;
            $kk = To::safe('key', trim($f[0]), true);
            $vv = $this->v(trim($f[1]));
            $vv = Filter::NS($NS . $kk . '_raw', e($vv), $lot);
            $output[$kk . '_raw'] = $vv;
            $vv = Filter::NS($NS . 'iota.input', $vv, $lot);
            $vv = Filter::NS($NS . $kk, $vv, $lot);
            $vv = Filter::NS($NS . 'iota.output', $vv, $lot);
            $output[$kk] = $vv;
        }
        return $output;
    }

    // Open the page file
    public function open($input) {
        $page = new Page;
        $page->open = $input;
        $i = 0;
        $output = [];
        $s = file($input, FILE_IGNORE_NEW_LINES);
        foreach ($s as $k => $v) {
            if ($i === 0 && $v === "") {
                continue;
            }
            if ($v === trim(S_B)) {
                unset($s[$k]);
                $i++;
                continue;
            }
            $output[$i][] = $v;
        }
        // has header data ...
        if (isset($output[0])) {
            foreach ($output[0] as $v) {
                $f = explode(S_I, $v, 2);
                $page->header[trim($f[0])] = trim($f[1] ?? "");
            }
            unset($output[0]);
        }
        foreach (array_values($output) as $k => $v) {
            $page->content[$k] = trim(implode(N, $v));
        }
        return $page;
    }

    // Add page header or update the existing page header data
    public function header($lot = [], $v = "") {
        if (!is_array($lot)) {
            $lot = [$this->x($lot) => $v];
        }
        foreach ($lot as $k => $v) {
            $kk = $this->x($k);
            if ($v === false) {
                unset($lot[$kk], $this->header[$kk]);
            } else {
                $lot[$kk] = $this->x(trim($v));
            }
        }
        Anemon::extend($this->header, $lot);
        return $this;
    }

    // Add page content or update the existing page content
    public function content($lot = "", $i = null) {
        if ($lot === false) {
            if ($i !== null) {
                unset($this->content[$i]);
            } else {
                $this->content = [];
            }
        }
        $this->content[$i === null ? $this->i : $i] = $this->x(trim($lot));
        $this->i++;
        return $this;
    }

    // Show page data as plain text
    public function put() {
        return $this->_create();
    }

    // Show page data as array
    public function read($content = 'content', $NS = 'page:') {
        if ($content === false) {
            $this->content = [];
        }
        return $this->text($this->_create(), $content, $NS);
    }

    // Save the opened page
    public function save($consent = 0600) {
        File::write($this->_create())->saveTo($this->open, $consent);
    }

    // Save the generated page to ...
    public function saveTo($path, $consent = 0600) {
        $this->open = $path;
        return $this->save($consent);
    }

}
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

    protected static $i = 0;

    // Remove `:` in field key
    protected static function _x($key) {
        return trim(str_replace(S, '_', $key));
    }

    // Reset the cached data
    protected static function _reset() {
        $this->open = null;
        $this->header = [];
        $this->content = [];
        $this->i = 0;
    }

    // Create the page
    protected static function _create() {
        $header = "";
        $_ = "\n\n" . SEPARATOR . "\n\n";
        foreach ($this->header as $k => $v) {
            $header .= $k . S . ' ' . $v . "\n";
        }
        $content = implode($_, $this->content);
        return trim(substr($header, 0, -1) . $_ . $content);
    }

    // Create from text
    public function text($text, $content = 'content', $FP = 'page:', $results = [], $data = []) {
        $c = $content !== false ? $content : 'content';
        $_ = SEPARATOR;
        foreach ($results as $k => $v) {
            $results[$k . '_raw'] = Filter::colon($FP . $k . '_raw', $v, $data);
            $results[$k] = Filter::colon($FP . $k, $v, $data);
        }
        if ( !$content) {
            // By file path
            if (strpos($text, ROOT) === 0 && ($text = File::open($text)->get($_)) !== false) {
                $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
                Anemon::extend($results, self::_header($text, $FP, $data));
            // By file content
            } else {
                $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
                if (strpos($text, $_) !== false) {
                    $s = explode($_, $text, 2);
                    Anemon::extend($results, self::_header(trim($s[0]), $FP, $data));
                    if (isset($s[1]) && $s[1] !== "") {
                        $results[$c . '_raw'] = trim($s[1]);
                    }
                }
            }
        } else {
            // By file path
            if (strpos($text, ROOT) === 0 && file_exists($text)) {
                $text = file_get_contents($text);
            }
            $text = Filter::apply($FP . 'input', Converter::RN($text), $FP, $data);
            // By file content
            if ($text === $_ || strpos($text, $_) === false) {
                $results[$c . '_raw'] = Converter::DS(trim($text));
            } else {
                $s = explode($_, $text, 2);
                Anemon::extend($results, self::_header(trim($s[0]), $FP, $data));
                if (isset($s[1]) && $s[1] !== "") {
                    $results[$c . '_raw'] = trim($s[1]);
                }
            }
        }
        unset($results['__'], $results['___raw']);
        Anemon::extend($data, $results);
        if (isset($results[$c . '_raw'])) {
            $content_x = explode($_, $results[$c . '_raw']);
            if (count($content_x) > 1) {
                $results[$c . '_raw'] = $results[$c] = [];
                $i = 0;
                foreach ($content_x as $v) {
                    $v = Converter::DS(trim($v));
                    $v = Filter::colon($FP . $c . '_raw', $v, $data, $i + 1);
                    $results[$c . '_raw'][$i] = $v;
                    $v = Filter::colon($FP . 'shortcode', $v, $data, $i + 1);
                    $v = Filter::colon($FP . $c, $v, $data, $i + 1);
                    $results[$c][$i] = $v;
                    $i++;
                }
            } else {
                $v = Converter::DS($results[$c . '_raw']);
                $v = Filter::colon($FP . $c . '_raw', $v, $data, 1);
                $results[$c . '_raw'] = $v;
                $v = Filter::colon($FP . 'shortcode', $v, $data, 1);
                $v = Filter::colon($FP . $c, $v, $data, 1);
                $results[$c] = $v;
            }
        }
        return Filter::apply($FP . 'output', $results, $FP, $data);
    }

    protected static function _header($text, $FP, $data) {
        $results = [];
        $headers = explode("\n", trim($text));
        foreach ($headers as $header) {
            $field = explode(S, $header, 2);
            if ( !isset($field[1])) $field[1] = 'false';
            $key = Text::parse(trim($field[0]), '->array_key', true);
            $value = Converter::DS(trim($field[1]));
            $value = Filter::colon($FP . $key . '_raw', Converter::strEval($value), $data);
            $results[$key . '_raw'] = $value;
            $value = Filter::colon($FP . $key, $value, $data);
            $results[$key] = $value;
        }
        return $results;
    }

    // Open the page file
    public function open($path) {
        self::_reset();
        $this->open = $path;
        $i = 0;
        $results = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $k => $v) {
            if ($i === 0 && $v === "") {
                continue;
            }
            if ($v === SEPARATOR) {
                unset($lines[$k]);
                $i++;
                continue;
            }
            $results[$i][] = $v;
        }
        // has header data ...
        if (isset($results[0])) {
            foreach ($results[0] as $v) {
                $field = explode(S, $v, 2);
                $this->header[trim($field[0])] = isset($field[1]) ? trim($field[1]) : "";
            }
            unset($results[0]);
        }
        foreach (array_values($results) as $k => $v) {
            $this->content[$k] = trim(implode("\n", $v));
        }
        return $this;
    }

    // Add page header or update the existing page header data
    public function header($data = [], $value = "") {
        if ( !is_array($data)) {
            $data = array(self::_x($data) => $value);
        }
        foreach ($data as $k => $v) {
            $kk = self::_x($k);
            if ($v === false) {
                unset($data[$kk], $this->header[$kk]);
            } else {
                // Restrict user(s) from inputting the `SEPARATOR` constant
                // to prevent mistake(s) in parsing the file content
                $data[$kk] = Converter::ES(trim($v));
            }
        }
        Anemon::extend($this->header, $data);
        return $this;
    }

    // Add page content or update the existing page content
    public function content($data = "", $i = null) {
        if ($data === false) {
            if ( !is_null($i)) {
                unset($this->content[$i]);
            } else {
                $this->content = [];
            }
        }
        // Restrict user(s) from inputting the `SEPARATOR` constant
        // to prevent mistake(s) in parsing the file content
        $this->content[is_null($i) ? $this->i : $i] = Converter::ES(trim($data));
        $this->i++;
        return $this;
    }

    // Show page data as plain text
    public function put() {
        $output = self::_create();
        self::_reset();
        return $output;
    }

    // Show page data as array
    public function read($content = 'content', $FP = 'page:') {
        if ($content === false) {
            $this->content = [];
        }
        $results = self::text(self::_create(), $content, $FP);
        self::_reset();
        return $results;
    }

    // Save the opened page
    public function save($permission = 0600) {
        File::write(self::_create())->saveTo($this->open, $permission);
        self::_reset();
    }

    // Save the generated page to ...
    public function saveTo($path, $permission = 0600) {
        $this->open = $path;
        return self::save($permission);
    }

}
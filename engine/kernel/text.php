<?php

class Text extends DNA {

    protected static $text = [];
    protected static $parser = [];

    /**
     * =====================================================================
     *  TEXT PARSER CREATOR
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::parser('to_upper_case', function($input) {
     *        return strtoupper($input);
     *    });
     *
     * ---------------------------------------------------------------------
     *
     */

    public function parser($name, $action) {
        $name = strtolower($name);
        if (strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        $this->parser[static::class][$name] = $action;
    }

    /**
     * =====================================================================
     *  TEXT PARSER EXISTENCE
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if ( !Text::parserExist('to_upper_case')) {
     *        Text::parser('to_upper_case', function($input) { ... });
     *    }
     *
     * ---------------------------------------------------------------------
     *
     */

    public function parserExist($name = null, $fail = false) {
        $c = static::class;
        if (is_null($name)) {
            return isset($this->parser[$c]) && !empty($this->parser[$c]) ? $this->parser[$c] : $fail;
        }
        $name = strtolower($name);
        if (strpos($name, 'to_') !== 0) $name = 'to_' . $name;
        return isset($this->parser[$c][$name]) ? $this->parser[$c][$name] : $fail;
    }

    /**
     * =====================================================================
     *  TEXT PARSER OUTPUT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    var_dump(Text::parse($input));
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input)->to_upper_case;
     *
     * ---------------------------------------------------------------------
     *
     *    echo Text::parse($input, '->upper_case');
     *
     * ---------------------------------------------------------------------
     *
     */

    public function parse() {
        $arguments = func_get_args();
        $c = static::class;
        // Alternate function for faster parsing process => `Text::parse('foo', '->html')`
        if (count($arguments) > 1 && is_string($arguments[1]) && strpos($arguments[1], '->') === 0) {
            $parser = str_replace('->', 'to_', strtolower($arguments[1]));
            unset($arguments[1]);
            return isset($this->parser[$c][$parser]) ? call_user_func_array($this->parser[$c][$parser], $arguments) : $arguments[0];
        }
        // Default function for complete parsing process => `Text::parse('foo')->to_html`
        $results = [];
        if ( !isset($this->parser[$c])) {
            $this->parser[$c] = [];
        }
        foreach ($this->parser[$c] as $name => $action) {
            $results[$name] = call_user_func_array($action, $arguments);
        }
        return (object) $results;
    }

    /**
     * =====================================================================
     *  INITIALIZE THE TEXT CHECKER
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    Text::check($text)-> ...
     *
     * ---------------------------------------------------------------------
     *
     */

    public function check($text) {
        $this->text = is_array($text) ? $text : func_get_args();
        return $this;
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` AND `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if (Text::check($text)->has('A', 'B')) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public function has($text) {
        $arguments = is_array($text) ? $text : func_get_args();
        if (count($arguments) === 1) {
            return strpos($this->text[0], $arguments[0]) !== false;
        }
        $text_v = 0;
        foreach ($arguments as $v) {
            if (strpos($this->text[0], $v) !== false) {
                $text_v++;
            }
        }
        return $text_v === count($arguments);
    }

    /**
     * =====================================================================
     *  CHECK IF TEXT CONTAIN(S) `A` OR `B`
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if (Text::check('A', 'B')->in($text)) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public function in($text) {
        $arguments = is_array($text) ? $text : func_get_args();
        if (count($this->text) === 1) {
            if (count($arguments) === 1) {
                if ($arguments[0] === "") return $this->text[0] === "";
                if ($this->text[0] === "") return $arguments[0] === "";
                return strpos($arguments[0], $this->text[0]) !== false;
            }
            foreach ($arguments as $v) {
                if (strpos($this->text[0], $v) !== false) return true;
            }
        }
        foreach ($this->text as $v) {
            if (strpos($arguments[0], $v) !== false) return true;
        }
        return false;
    }

    /**
     * =====================================================================
     *  CHECK OFFSET OF A STRING INSIDE A TEXT
     * =====================================================================
     *
     * -- CODE: ------------------------------------------------------------
     *
     *    if (Text::check($text)->offset('A')->start === 0) { ... }
     *
     * ---------------------------------------------------------------------
     *
     */

    public function offset($text) {
        $output = array('start' => -1, 'end' => -1);
        if (($offset = strpos($this->text[0], $text)) !== false) {
            $output['start'] = $offset;
            $output['end'] = $offset + strlen($text) - 1;
        }
        return (object) $output;
    }

}
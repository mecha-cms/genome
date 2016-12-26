<?php

class UI extends Genome {

    public static function _decor($attr, $c = []) {
        $c = (array) $c;
        if (isset($attr['class'])) {
            $attr['classes'] = array_merge([$attr['class']], $c);
            unset($attr['class']);
        } else if (isset($attr['classes'])) {
            $attr['classes'] = array_merge($attr['classes'], $c);
        } else {
            $attr['classes'] = $c;
        }
        $attr['classes'] = array_unique($attr['classes']);
        return $attr;
    }

    public static function button() {}
    public static function btn() {}

    public static function input($name = null, $type = 'text', $value = null, $placeholder = null, $attr = [], $dent = 0) {
        return call_user_func('Form::' . $type, $name, $value, $placeholder, self::_decor($attr, ['input', 'input--' . $type]), $dent);
    }

    public static function checkbox($name = null, $value = null, $check = null, $text = "", $attr = [], $dent = 0) {
        return Form::checkbox($name, $value, $check, $text, self::_decor($attr, ['input', 'input--checkbox']), $dent);
    }

    public static function radio($name = null, $options = [], $select = null, $attr = [], $dent = 0) {
        return Form::radio($name, $options, $select, self::_decor($attr, ['input', 'input--radio']), $dent);
    }

    public static function select() {}
    public static function textarea() {}
    public static function tab() {}
    public static function page() {}
    public static function item() {}
    public static function modal() {}
    public static function drop() {}
    public static function table() {}
    public static function table_data() {}

}
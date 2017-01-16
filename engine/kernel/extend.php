<?php

class Extend extends Genome {

    public static function version($id, $v = null) {
        return Mecha::version($v, self::info($id)->version('0.0.0'));
    }

    public static function info($id) {
        global $config, $language;
        // Check whether the localized “about” file is available
        $f = EXTEND . DS . $id . DS;
        if (!$info = File::exist($f . 'about.' . $config->language . '.page')) {
            $info = $f . 'about.page';
        }
        return new Page($info, [
            'id' => Folder::exist($f) ? $id : null,
            'title' => To::title($id),
            'author' => $language->anonymous,
            'version' => '0.0.0',
            'content' => $language->_message_avail($language->description)
        ], strtolower(static::class));
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(EXTEND . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: EXTEND;
        $state = $folder . DS . $id . DS . 'lot' . DS . 'state' . DS . 'config.php';
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $state = include $state;
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}
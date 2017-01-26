<?php

class Plugin extends Extend {

    public static function info($id) {
        global $config, $language;
        $f = PLUGIN . DS . $id . DS;
        return new Page(File::exist([
            // Check whether the localized “about” file is available
            $f . 'about.' . $config->language . '.page',
            // Use the default “about” file if available
            $f . 'about.page'
        ], null), [
            'id' => Folder::exist($f) ? $id : null,
            'title' => To::title($id),
            'author' => $language->anonymous,
            'version' => '0.0.0',
            'content' => $language->_message_avail($language->description)
        ], __c2f__(static::class));
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(PLUGIN . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: PLUGIN;
        return parent::state($id, $key, $fail, $folder);
    }

}
<?php

class Plugin extends Extend {

    public static function info($id) {
        extract(Lot::get(null, []));
        // Check whether the localized “about” file is available
        $f = PLUGIN . DS . 'lot' . DS . 'worker' . DS . $id . DS;
        if (!$info = File::exist($f . 'about.' . $config->language . '.txt')) {
            $info = $f . 'about.txt';
        }
        return new Page($info, "", [
            'id' => Folder::exist($f) ? $id : null,
            'title' => To::title($id),
            'author' => $language->anonymous,
            'type' => 'HTML',
            'version' => '0.0.0',
            'content' => $language->_message_avail($language->description)
        ], strtolower(static::class));
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(PLUGIN . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: PLUGIN . DS . 'lot' . DS . 'worker';
        return parent::state($id, $key, $fail, $folder);
    }

}
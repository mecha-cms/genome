<?php

Content::_('exist', function(string $id, $active = true) {
    $exist = is_dir(static::$config['folder'] . DS . $id);
    return $active ? ($exist && Config::get('shield') === $id) : $exist;
});
<?php

Content::_('exist', function(string $id, $active = true) {
    $exist = is_dir(static::$config['root'] . DS . $id);
    return $active ? ($exist && Config::get('shield') === $id) : $exist;
});

if (Extend::exist('message')) {
    Content::_('message', function(string $kin = "") {
        if ($message = Message::get($kin, false)) {
            $message = str_replace([
                '<message type="',
                '</message>'
            ], [
                '<p class="message message-',
                '</p>'
            ], $message);
            echo '<div class="messages p">' . $message . '</div>';
        }
    });
}
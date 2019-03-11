<?php

if (Extend::exist('message')) {
    Shield::_('message', function(string $kin = "") {
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
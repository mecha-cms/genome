<?php

Message::plug('error', function($text, $a = []) {
    ++Message::$x;
    return Message::set('error', $text, $a);
});

Message::plug('warning', function($text, $a = []) {
    ++Message::$x;
    return Message::set('warning', $text, $a);
});
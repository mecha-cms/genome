<?php

Message::_('error', function($text, $vars = [], $preserve_case = false) {
    return Message::halt('error', $text, $vars, $preserve_case);
});
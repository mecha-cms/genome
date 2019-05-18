<?php

function message(...$v) {
    Message::info(...$v);
}

$GLOBALS['message'] = $message = new Message;
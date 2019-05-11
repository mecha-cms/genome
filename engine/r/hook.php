<?php

function hook(...$v) {
    return count($v) < 2 ? Hook::get(...$v) : Hook::set(...$v);
}
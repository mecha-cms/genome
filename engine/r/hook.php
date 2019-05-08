<?php

function hook(...$v) {
    return count($v) > 1 ? Hook::set(...$v) : Hook::get(...$v);
}
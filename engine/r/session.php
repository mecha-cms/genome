<?php

Session::start();

function session(...$v) {
    return count($v) > 1 ? Session::set(...$v) : Session::get(...$v);
}
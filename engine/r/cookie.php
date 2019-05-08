<?php

function cookie(...$v) {
    return count($v) > 1 ? Config::set(...$v) : Config::get(...$v);
}
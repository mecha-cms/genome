<?php

function cache(...$v) {
    return count($v) > 1 ? Cache::hit(...$v) : Cache::get(...$v);
}
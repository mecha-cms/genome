<?php

function page(...$v) {
    return new Page(...$v);
}

function pages(...$v) {
    return _\get\pages(...$v);
}
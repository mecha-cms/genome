<?php

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $GLOBALS['l'] = new Language;
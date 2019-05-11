<?php

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $language = new Language;
<?php

Language::set([
    'file' => ['File', 'Files', 'Files'],
    'folder' => ['Folder', 'Folders', 'Folders']
]);

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $language = new Language;
<?php

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $language = new Language;

Language::set([
    'cache' => ['Cache', 'Cache', 'Caches'],
    'config' => ['Configuration', 'Configuration', 'Configurations'],
    'extension' => ['Extension', 'Extension', 'Extensions'],
    'file' => ['File', 'File', 'Files'],
    'folder' => ['Folder', 'Folder', 'Folders'],
    'state' => ['State', 'State', 'States'],
    'trash' => ['Trash', 'Trash', 'Trashes']
]);
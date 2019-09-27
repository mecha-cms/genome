<?php

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $language = new Language;

Language::set([
    'blob' => ['Blob', 'Blob', 'Blobs'],
    'cache' => ['Cache', 'Cache', 'Caches'],
    'extension' => ['Extension', 'Extension', 'Extensions'],
    'file' => ['File', 'File', 'Files'],
    'folder' => ['Folder', 'Folder', 'Folders'],
    'state' => ['State', 'State', 'States'],
    'trash' => ['Trash', 'Trash', 'Trashes']
]);
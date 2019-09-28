<?php

function language(...$v) {
    return $GLOBALS['language'](...$v);
}

$GLOBALS['language'] = $language = new Language;

Language::set([
    'blob' => ['Blob', 'Blob', 'Blobs'],
    'cache' => ['Cache', 'Cache', 'Caches'],
    'direction' => ['Direction', 'Direction', 'Directions'],
    'extension' => ['Extension', 'Extension', 'Extensions'],
    'file' => ['File', 'File', 'Files'],
    'folder' => ['Folder', 'Folder', 'Folders'],
    'language' => ['Language', 'Language', 'Languages'],
    'locale' => ['Locale', 'Locale', 'Locales'],
    'state' => ['State', 'State', 'States'],
    'trash' => ['Trash', 'Trash', 'Trashes'],
    'zone' => ['Zone', 'Zone', 'Zones']
]);
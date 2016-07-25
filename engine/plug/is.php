<?php

Is::plug('json', function($input) {
    if (!is_string($input) || !trim($input)) return false;
    return (
        // Maybe an empty string, array or object
        $input === '""' ||
        $input === '[]' ||
        $input === '{}' ||
        // Maybe an encoded JSON string
        $input[0] === '"' ||
        // Maybe a flat array
        $input[0] === '[' ||
        // Maybe an associative array
        strpos($input, '{"') === 0
    ) && json_decode($input) !== null && json_last_error() !== JSON_ERROR_NONE
});

Is::plug('serialize', function($input) {
    if(!is_string($input) || !trim($input)) return false;
    return $input === 'N;' || strpos($input, 'a:') === 0 || strpos($input, 'b:') === 0 || strpos($input, 'd:') === 0 || strpos($input, 'i:') === 0 || strpos($input, 's:') === 0 || strpos($input, 'O:') === 0;
});
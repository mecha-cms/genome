<?php

// Wrap description data with paragraph tags if needed
Hook::set('page.output', function($data) {
    if (isset($data['description']) && strpos($data['description'], '</p>') === false) {
        $data['description'] = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($data['description']))) . '</p>';
    }
    return $data;
});
<?php

Hook::set('page.output', function($data) {
    // Wrap description data with paragraph tag(s) if needed
    if (!empty($data['description']) && strpos($data['description'], '</p>') === false) {
        $data['description'] = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($data['description']))) . '</p>';
    }
    return $data;
});

// Add CSS file to the `<head>` section …
Asset::set('css/document.min.css');

// Add JS file to the `<body>` section …
Asset::set('css/document.min.js');
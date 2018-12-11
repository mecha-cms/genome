<?php

Hook::set('page.description', function($description) {
    // Wrap description data with paragraph tag(s) if needed
    if ($description && strpos($description, '</p>') === false) {
        return '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($description))) . '</p>';
    }
    return $description;
});

// Add CSS file to the `<head>` section…
Asset::set('css/document.min.css', 20);

// Add JS file to the `<body>` section…
Asset::set('js/document.min.js', 20);
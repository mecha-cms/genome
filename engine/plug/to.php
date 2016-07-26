<?php

To::plug('html_x', function($input) {
    return htmlspecialchars($input);
});

To::plug('html_v', function($input) {
    return htmlspecialchars_decode($input);
});

To::plug('url_x', function($input) {
    return urlencode($input);
});

To::plug('url_v', function($input) {
    return urldecode($input);
});

To::plug('json', function($input) {
    return json_encode($input);
});

To::plug('anemon', function($input) {
    return (array) json_decode($input, true);
});
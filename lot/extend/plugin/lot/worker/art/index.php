<?php

function fn_art($data) {
    // Add `<style>` tag(s) if it is not there
    if (!empty($data['css']) && stripos($data['css'], '</style>') === false) {
        $data['css'] = '<style media="screen">' . N . trim($data['css']) . N . '</style>';
    }
    // Add `<script>` tag(s) if it is not there
    if (!empty($data['js']) && stripos($data['js'], '</script>') === false) {
        $data['js'] = '<script>' . N . trim($data['js']) . N . '</script>';
    }
    return $data;
}

function fn_art_set($content) {
    extract(Lot::get(null, []));
    // Append custom CSS before `</head>` …
    $content = str_ireplace('</head>', $page->css . '</head>', $content);
    // Append custom JS before `</body>` …
    $content = str_ireplace('</body>', $page->js . '</body>', $content);
    return $content;
}

Hook::set('page.output', 'fn_art');
Hook::set('shield.output', 'fn_art_set');
<?php

Hook::set('page.output', function($data) {
    // Wrap description data with paragraph tag(s) if needed
    if (!empty($data['description']) && strpos($data['description'], '</p>') === false) {
        $data['description'] = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($data['description']))) . '</p>';
    }
    // Add `<style>` and `<script>` tag(s) if it is not there
    if (!empty($data['css']) && strpos($data['css'], '</style>') === false) {
        $data['css'] = '<style media="screen">' . N . trim($data['css']) . N . '</style>';
    }
    if (!empty($data['js']) && strpos($data['js'], '</script>') === false) {
        $data['js'] = '<script>' . N . trim($data['js']) . N . '</script>';
    }
    return $data;
});
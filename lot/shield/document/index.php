<?php

Hook::set('page.output', function($data) {
    // Wrap description data with paragraph tag(s) if needed
    if (!empty($data['description']) && strpos($data['description'], '</p>') === false) {
        $data['description'] = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($data['description']))) . '</p>';
    }
    // Add `<style>` tag(s) if it is not there
    if (!empty($data['css']) && stripos($data['css'], '</style>') === false) {
        $data['css'] = '<style media="screen">' . N . trim($data['css']) . N . '</style>';
    }
    // Add `<script>` tag(s) if it is not there
    if (!empty($data['js']) && stripos($data['js'], '</script>') === false) {
        $data['js'] = '<script>' . N . trim($data['js']) . N . '</script>';
    }
    return $data;
});
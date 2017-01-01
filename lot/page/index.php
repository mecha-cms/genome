<?php

Hook::set('page.input', function($data) {
    if (!empty($data['content']) && strpos($data['content'], '[connect:') !== false) {
        $data['content'] = str_replace('[connect:', '**Related:** [link:', $data['content']);
    }
    return $data;
}, .9);
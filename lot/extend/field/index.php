<?php

if ($fields = g(STATE . DS . 'field' . DS . 'page', 'php', "", false)) {
    $grid = ['class' => 'grid-data'];
    $grid_key = ['class' => 'grid-data-key'];
    $grid_value = ['class' => 'col-data-value'];
    foreach ($fields as $field) {
        $data = require $field;
        $key = Path::N($field);
        $title = isset($data['title']) ? $data['title'] : "";
        $type = isset($data['type']) ? $data['type'] : false;
        $value = isset($data['value']) ? $data['value'] : null;
        $placeholder = isset($data['placeholder']) ? $data['placeholder'] : null;
        $pattern = isset($data['pattern']) ? $data['pattern'] : null;
        $required = isset($data['required']) && $data['required'];
        $checked = isset($data['checked']) && $data['checked'];
        $html = "";
        if ($type === 'text') {
            $html .= HTML::begin('label', $grid) . N;
            $html .= HTML::begin('span', $grid_key, 1) . N;
            $html .= $title . N;
            $html .= HTML::end() . N;
            $html .= HTML::begin('span', $grid_value, 1) . N;
            $html .= UI::text($key, $value, $placeholder, [
                'class' => 'block',
                'pattern' => $pattern
            ], 2) . N;
            $html .= HTML::end() . N;
            $html .= HTML::end() . N;
        } else if ($type === 'options') {
            $html .= HTML::begin('div', $grid) . N;
            $html .= HTML::begin('span', $grid_key, 1) . N;
            $html .= $title . N;
            $html .= HTML::end() . N;
            $html .= HTML::begin('span', $grid_value, 1) . N;
            if (!empty($data['options'])) {
                $html_array = [];
                foreach ($data['options'] as $k => $v) {
                    $html_array[] = UI::checkbox($key . '[' . $k . ']', $k, is_array($checked) ? Is::these($checked)->has($k) : (is_string($checked) ? $k === $checked : $checked), $v, [], 2);
                }
                $html .= implode(N . HTML::br(1, [], 2) . N, $html_array) . N;
            }
            $html .= HTML::end() . N;
            $html .= HTML::end() . N;
        } else {
            
        }
        // echo $html;
    }
}
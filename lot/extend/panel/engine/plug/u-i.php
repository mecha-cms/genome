<?php

// `<input type="(color|date|email|number|password|range|search|tel|text|url)">`
foreach (['color', 'date', 'email', 'number', 'password', 'range', 'search', 'tel', 'text', 'url'] as $unit) {
    UI::plug($unit, function($name = null, $value = null, $placeholder = null, $attr = [], $dent = 0) use($unit) {
        return UI::input($name, $unit, $value, $placeholder, $attr, $dent);
    });
}
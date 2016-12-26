<?php

HTML::plug('br', function($i = 1, $attr = [], $dent = 0) {
    return HTML::dent($dent) . str_repeat(HTML::unite('br', false, $attr), $i);
});
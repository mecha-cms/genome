<?php

Shield::plug('chunk', function($input, $fail = false, $buffer = true) {
    $path__ = To::path($input);
    $G = ['name' => $input];
    $NS = 'shield.chunk.';
    if (__is_anemon__($fail)) {
        Shield::$lot = array_merge(Shield::$lot, $fail);
        $fail = false;
    }
    $path__ = Hook::NS($NS . 'path', [], Shield::path($path__, $fail));
    $G['lot'] = Shield::$lot;
    $G['path'] = $path__;
    $out = "";
    if ($path__) {
        // Begin chunk
        Hook::fire($NS . 'lot.b', [$G, $G]);
        extract(Hook::fire($NS . 'lot', [], Shield::$lot));
        Hook::fire($NS . 'lot.e', [$G, $G]);
        Hook::fire($NS . 'b', [$G, $G]);
        if ($buffer) {
            ob_start(function($content) use($path__, &$out) {
                $content = Hook::NS($NS . 'i', [$path__], $content);
                $out = Hook::NS($NS . 'o', [$path__], $content);
                return $out;
            });
            require $path__;
            ob_end_flush();
        } else {
            require $path__;
        }
        $G['content'] = $out;
        // End chunk
        Hook::fire($NS . 'e', [$G, $G]);
    }
});
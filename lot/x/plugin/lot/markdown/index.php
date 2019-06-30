<?php

namespace _\type\markdown {
    function b($in, $mode = 'text') {
        $parser = new \ParsedownExtraPlugin;
        foreach (\plugin('markdown') as $k => $v) {
            $parser->{$k} = $v;
        }
        return $parser->{$mode}((string) $in);
    }
    function i($in) {
        if ($this['type'] !== 'Markdown') {
            return $in;
        }
        return \w(b($in), HTML_WISE_I);
        // return b($in, $lot, 'line'); // TODO
    }
    \Hook::set('*.title', __NAMESPACE__ . "\\i", 2);
    \Hook::set(['*.description', '*.content'], __NAMESPACE__, 2);
}

namespace _\type {
    function markdown($in = "") {
        if ($this['type'] !== 'Markdown') {
            return $in;
        }
        return markdown\b($in);
    }
}

namespace {
    Language::set('o:page-type.Markdown', 'Markdown');
    // Add `markdown` to the allowed file extension(s)
    File::$config['x']['markdown'] = 1;
    File::$config['x']['md'] = 1;
    File::$config['x']['mkd'] = 1;
}
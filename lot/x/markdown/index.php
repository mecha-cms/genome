<?php

namespace _\lot\x\markdown {
    function b($in, $mode = 'text') {
        $parser = new \ParsedownExtraPlugin;
        foreach (\state('markdown') as $k => $v) {
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
    \Hook::set('page.title', __NAMESPACE__ . "\\i", 2);
    \Hook::set(['page.description', 'page.content'], __NAMESPACE__, 2);
}

namespace _\lot\x {
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
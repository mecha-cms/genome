<?php

namespace _\markdown {
    function b($in, array $lot = [], $mode = 'text') {
        $x = new \ParsedownExtraPlugin;
        foreach (\Plugin::state('markdown') as $k => $v) {
            $x->{$k} = $v;
        }
        return $x->{$mode}((string) $in);
    }
    function i($in, array $lot = []) {
        if ($this['type'] !== 'Markdown') {
            return $in;
        }
        return \w(b($in, $lot), HTML_WISE_I);
        // return b($in, $lot, 'line'); // TODO
    }
    \Hook::set('*.title', __NAMESPACE__ . "\\i", 2);
    \Hook::set(['*.description', '*.content'], __NAMESPACE__, 2);
}

namespace _ {
    function markdown($in = "", array $lot = []) {
        if ($this['type'] !== 'Markdown') {
            return $in;
        }
        return markdown\b($in, $lot);
    }
}

namespace {
    Language::set('o:page-type.Markdown', 'Markdown');
    From::_('markdown', function(string $in = "", $span = false) {
        return _\markdown\b($in, $span ? 'span' : 'text');
    });
    To::_('markdown', function(string $in = "") {
        return $in; // TODO
    });
    // Alias(es)
    From::_('Markdown', From::_('markdown'));
    To::_('Markdown', To::_('markdown'));
    // Add `markdown` to the allowed file extension(s)
    File::$config['x'] = concat(File::$config['x'], [
        'markdown',
        'md',
        'mkd'
    ]);
}
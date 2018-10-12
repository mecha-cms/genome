<?php

namespace fn\markdown {
    function b($in = "", array $lot = [], $mode = 'text') {
        $x = new \ParsedownExtraPlugin;
        foreach (\Plugin::state('markdown') as $k => $v) {
            $x->{$k} = $v;
        }
        return $x->{$mode}($in);
    }
    function i($in = "", array $lot = []) {
        if ($this->type !== 'Markdown') {
            return $in;
        }
        return \w(b($in, $lot), HTML_WISE_I);
        // return b($in, 'line'); // TODO
    }
    \Hook::set('*.title', __NAMESPACE__ . '\i', 2);
    \Hook::set(['*.description', '*.content'], __NAMESPACE__, 2);
}

namespace fn {
    function markdown($in = "", array $lot = []) {
        if ($this->type !== 'Markdown') {
            return $in;
        }
        return markdown\b($in, $lot);
    }
}

namespace {
    From::_('Markdown', function(string $in = "", $span = false) {
        return fn\markdown\b($in, $span ? 'span' : 'text');
    });
    To::_('Markdown', function(string $in = "") {
        return $in; // TODO
    });
    // Alias(es)
    From::_('markdown', From::_('Markdown'));
    To::_('markdown', To::_('Markdown'));
    // Add `markdown` to the allowed file extension(s)
    File::$config['extension'] = concat(File::$config['extension'], [
        'markdown',
        'md',
        'mkd'
    ]);
}
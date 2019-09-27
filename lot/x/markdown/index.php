<?php

namespace _\lot\x\markdown {
    function title($content) {
        $type = $this->type;
        if ($type !== 'Markdown' && $type !== 'text/markdown') {
            return $content;
        }
        $parser = new \ParsedownExtraPlugin;
        foreach ((array) \State::get('x.markdown') as $k => $v) {
            if (\strpos($k, 'block') === 0) {
                continue;
            }
            $parser->{$k} = $v;
        }
        return $parser->line($content ?? "");
    }
    \Hook::set([
        'page.title'
    ], __NAMESPACE__ . "\\title", 2);
}

namespace _\lot\x {
    function markdown($content) {
        $type = $this->type;
        if ($type !== 'Markdown' && $type !== 'text/markdown') {
            return $content;
        }
        $parser = new \ParsedownExtraPlugin;
        foreach ((array) \State::get('x.markdown') as $k => $v) {
            $parser->{$k} = $v;
        }
        return $parser->text($content ?? "");
    }
    // Add `text/markdown` to the file type list
    \File::$state['type']['text/markdown'] = 1;
    // Add `markdown` to the file extension list
    \File::$state['x']['markdown'] = 1;
    \File::$state['x']['md'] = 1; // Alias
    \File::$state['x']['mkd'] = 1; // Alias
    \Hook::set([
        'page.content',
        'page.description'
    ], __NAMESPACE__ . "\\markdown", 2);
}
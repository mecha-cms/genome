<?php

namespace _\lot\x\markdown {
    function title($content) {
        $type = $this->type;
        if ($type !== 'Markdown' && $type !== 'text/markdown') {
            return $content;
        }
        $parser = new \ParsedownExtraPlugin;
        foreach (\state('markdown', 'parsedown') as $k => $v) {
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
        foreach (\state('markdown', 'parsedown') as $k => $v) {
            $parser->{$k} = $v;
        }
        return $parser->text($content ?? "");
    }
    // Add `text/markdown` to the file type list
    \File::$config['type']['text/markdown'] = 1;
    // Add `markdown` to the file extension list
    \File::$config['x']['markdown'] = 1;
    \File::$config['x']['md'] = 1; // Alias
    \File::$config['x']['mkd'] = 1; // Alias
    \Hook::set([
        'page.content',
        'page.description'
    ], __NAMESPACE__ . "\\markdown", 2);
}
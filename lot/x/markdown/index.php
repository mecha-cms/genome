<?php namespace _\lot\x;

function markdown($content) {
    $type = $this->type;
    if ($type !== 'Markdown' && $type !== 'text/markdown') {
        return $content;
    }
    $parser = new \ParsedownExtraPlugin;
    foreach (\state('markdown') as $k => $v) {
        $parser->{$k} = $v;
    }
    return $parser->text($content);
}

// Add `markdown` to the file extension list
\File::$config['x']['markdown'] = 1;
\File::$config['x']['md'] = 1; // Alias
\File::$config['x']['mkd'] = 1; // Alias

\Hook::set([
    'page.content',
    'page.description',
    'page.title'
], __NAMESPACE__ . "\\markdown", 2);

// Remove block-level tag(s) from page title
\Hook::set('page.title', function($title) {
    return \w($title, 'abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,svg,time,u,var');
}, 2.1);

\Language::set('o:page.type.text/markdown', 'Markdown');
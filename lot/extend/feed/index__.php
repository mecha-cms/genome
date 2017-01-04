<?php

function fn_rss_item($page, $content = false) {
    echo '<item>';
    echo '<title><![CDATA[' . To::text($page->title) . ']]></title>';
    echo '<link>' . $page->url . '</link>';
    echo '<description><![CDATA[' . To::text($page->description) . ']]></description>';
    // if ($content) {
    //     echo '<content:encoded><![CDATA[' . $page->content . ']]></content:encoded>';
    // }
    echo '<pubDate>' . $page->date('r') . '</pubDate>';
    echo '<guid>' . $page->url . '</guid>';
    echo '</item>';
}

Route::hook('%*%', function($path) use($config, $url) {
    $p = explode('/', $path);
    $path = rtrim(PAGE . DS . Path::D($path), DS);
    $state = Extend::state(__DIR__);
    if (end($p) === $state['slug']['rss'] && $page = File::exist([
        $path . '.page',
        $path . DS . Path::B($path) . '.page',
        $path . DS . $config->slug . '.page',
        $path . DS . Path::B($path) . DS . $config->slug . '.page'
    ])) {
        $page = new Page($page);
        HTTP::mime('text/xml', $config->charset);
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        echo '<channel>';
        echo '<generator>Mecha ' . Mecha::version() . '</generator>';
        echo '<title><![CDATA[' . To::text(($page->title ? $page->title . ' · ' : "") . $config->title) . ']]></title>';
        echo '<link>' . To::url($path) . '/</link>';
        echo '<description><![CDATA[' . To::text($page->description ?: $config->description) . ']]></description>';
        echo '<lastBuildDate>' . (new Date())->format('r') . '</lastBuildDate>';
        echo '<language>' . $config->language . '</language>';
        if ($files = g($path, 'page')) {
            foreach ($files as $file) {
                fn_rss_item(new Page($file));
            }
        } else {
            fn_rss_item($page, true);
        }
        echo '</channel>';
        echo '</rss>';
        exit;
    }
});
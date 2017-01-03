<?php

Route::hook('%*%', function($path) use($config, $url) {
    $p = explode('/', $path);
    $path = PAGE . DS . Path::D($path);
    if (end($p) === 'rss.xml' && $page = File::exist([$path . '.page', $path . DS . Path::B($path) . '.page'])) {
        HTTP::mime('text/xml', $config->charset);
        $title = Page::open($page)->get('title');
        $title_r = To::text($config->title);
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        echo '<channel>';
        echo '<generator>Mecha ' . Mecha::version() . '</generator>';
        echo '<title><![CDATA[' . To::text(($title ? $title . ' ⸱ ' : "") . $title_r) . ']]></title>';
        echo '<link>' . $url->current . '/</link>';
        echo '<description><![CDATA[' . To::text($config->description) . ']]></description>';
        echo '<lastBuildDate>' . (new Date())->format('r') . '</lastBuildDate>';
        if ($files = g($path, 'page')) {
            foreach ($files as $file) {
                $page = new Page($file);
                $title = To::text($page->title);
                echo '<item>';
                echo '<title><![CDATA[' . $title . ']]></title>';
                echo '<link>' . $page->url . '</link>';
                echo '<description><![CDATA[' . $page->description . ']]></description>';
                echo '<pubDate>' . (new Date($page->time))->format('r') . '</pubDate>';
                echo '<guid>' . $page->url . '</guid>';
                echo '<source url="' . $page->url . '"><![CDATA[' . $title_r . ': ' . $title . ']]></source>';
                echo '</item>';
            }
        }
        echo '</channel>';
        echo '</rss>';
        exit;
    }
});
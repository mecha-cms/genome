<?php

Route::set(['%s/%i', '%s', ""], function($path = "", $offset = 1) use($config) {
    if ($path === "") {
        $path = 'index';
    }
    $folder = rtrim(PAGE . DS . $path, DS);
    // from a file ...
    if ($file = File::exist([$folder . '.page', $folder . '.archive'])) {
        $page = new Page($file);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &ndash; '));
        Seed::set('page', $page);
        if ($files = Anemon::eat(Get::pages($folder, [], 1, 'slug'))->chunk(5, $offset - 1)) {
            $pages = [];
            foreach ($files as $file) {
                $pages[] = new Page($file['path']);
            }
            Seed::set([
                'pager' => null,
                'pages' => $pages
            ]);
            Shield::attach('pages');
        }
        Shield::attach('page');
    }
});
<?php

Route::set(['%%/%i%', '%%', ""], function($path = "", $offset = 1) use($config, $url) {
    --$offset; // 0–based index
    $folder = rtrim(PAGE . DS . To::path($path === "" ? 'index' : $path), DS);
    // from a file ...
    if ($file = File::exist([$folder . '.page', $folder . '.archive'])) {
        $page = new Page($file);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &ndash; '));
        Seed::set('page', $page);
        if ($files = Get::pages($folder, [], 1, 'time', 'path')) {
            $pages = [];
            foreach (Anemon::eat($files)->chunk($config->chunk, $offset) as $file) {
                $pages[] = new Page($file);
            }
            if (empty($pages)) {
                Shield::abort(['204-pages', '404-pages', '204', '404']);
            }
            Seed::set([
                'pager' => new Elevator($files, $config->chunk, $offset, $url . '/' . $path),
                'pages' => $pages
            ]);
            Shield::attach('pages');
        }
        Shield::attach('page');
    // from a folder ...
    } else if (Is::D($folder)) {
        if ($file = File::exist([$folder . '.page', $folder . '.archive'])) {
            $page = new Page($file);
        } else {
            $page = false;
        }
        if ($files = Get::pages($folder, [], 1, 'time', 'path')) {
            $pages = [];
            foreach (Anemon::eat($files)->chunk($config->chunk, $offset) as $file) {
                $pages[] = new Page($file);
            }
        }
        if (empty($pages)) {
            Shield::abort(['204-pages', '404-pages', '204', '404']);
        }
        Config::set('page.title', new Anemon($page ? [$page->title, $config->title] : [$config->title], ' &ndash; '));
        Seed::set([
            'pager' => new Elevator($files, $config->chunk, $offset, $url . '/' . $path),
            'page' => $page,
            'pages' => $pages
        ]);
        Shield::attach('pages');
    }
});
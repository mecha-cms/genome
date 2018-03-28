<?php

// Require the plug manually…
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';

// Include worker(s)…
r(__DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker', [
    'config.php',
    'hook.php'
], null, Lot::get(null, []));

$state = Extend::state('page');

function fn_page_url($content, $lot = []) {
    if (!isset($lot['path'])) {
        return $content;
    }
    global $url;
    $s = Path::F($lot['path'], PAGE);
    return rtrim($url . '/' . ltrim(To::URL($s), '/'), '/');
}

Hook::set('page.url', 'fn_page_url', 1);

Lot::set([
    'page' => new Page,
    'pager' => new Elevator,
    'pages' => [],
    'parent' => new Page
]);

Route::set(['%*%/%i%', '%*%', ""], function($path = "", $step = null) use($state) {
    // Include global variable(s)…
    extract(Lot::get(null, []));
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = str_replace('../', "", urldecode($path));
    $path_canon = ltrim($path === "" ? $site->path : $path, '/');
    if ($step === 1 && !$url->query && $path === $site->path) {
        Message::info('kick', '<code>' . $url->current . '</code>');
        Guardian::kick(""); // Redirect to home page…
    }
    $folder = rtrim(PAGE . DS . To::path($path_canon), DS);
    $name = Path::B($folder);
    $i = ($h = $step ?: 1) - 1; // 0-based index…
    // Horizontal elevator…
    $elevator = [
        'direction' => [
           '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
           '-2' => [
                2 => ['rel' => null]
            ],
           '-1' => [
                1 => Elevator::WEST,
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => Elevator::EAST,
                2 => ['rel' => 'next']
            ]
        ]
    ];
    $pages = $page = [];
    Config::set('page.title', new Anemon([$site->title], ' &#x00B7; '));
    if ($file = $site->is('page')) {
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $k = PAGE;
        $sort = isset($site->page->sort) ? $site->page->sort : [1, 'path'];
        $chunk = isset($site->page->chunk) ? $site->page->chunk : 5;
        foreach (explode('/', '/' . $path) as $v) {
            $k .= $v ? DS . $v : "";
            if ($f = File::exist([
                $k . '.page',
                $k . '.archive'
            ])) {
                $f = new Page($f);
                $sort = $f->sort($sort);
                $chunk = $f->chunk($chunk);
            }
            if ($fn = File::exist($k . DS . 'index.php')) include $fn;
            if ($fn = File::exist($k . DS . 'index__.php')) include $fn;
        }
        $page = new Page($file);
        // Create elevator for single page mode
        $_folder = Path::D($folder);
        $_path = Path::D($path);
        if ($_file = File::exist([
            $_folder . '.page', // `lot\page\parent-slug.page`
            $_folder . '.archive', // `lot\page\parent-slug.archive`
            $_folder . DS . '$.page', // `lot\page\parent-slug\$.page`
            $_folder . DS . '$.archive' // `lot\page\parent-slug\$.archive`
        ])) {
            $_page = new Page($_file);
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $_page->sort($sort);
            $chunk = $_page->chunk($chunk);
            $_files = Get::pages($_folder, 'page', $sort, 'slug');
        } else {
            $_files = [];
        }
        Lot::set([
            'page' => $page,
            'pager' => new Elevator($_files, null, $page->slug, $url . '/' . $_path, $elevator, 'page'),
            'parent' => new Page($_file)
        ]);
        Config::set('page.title', new Anemon([$page->title, $site->title], ' &#x00B7; '));
        if (!$site->is('pages')) {
            // Page(s) view has been disabled!
        } else if ($files = Get::pages($folder, 'page', $sort, 'path')) {
            if ($query = l(Request::get($site->q, ""))) {
                Config::set('page.title', new Anemon([$language->search . ': ' . $query, $page->title, $site->title], ' &#x00B7; '));
                $query = explode(' ', $query);
                Config::set('is.search', true);
                $files = array_filter($files, function($v) use($query) {
                    $v = Path::N($v);
                    foreach ($query as $q) {
                        if (strpos($v, $q) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            $files = array_values($files);
            foreach (Anemon::eat($files)->chunk($chunk, $i) as $file) {
                $pages[] = new Page($file);
            }
            Config::set('has.next', count($files) > $h * $chunk);
            if (empty($pages)) {
                // Greater than the maximum step or less than `1`, abort!
                Config::set('is.error', 404);
                Config::set('has.next', false);
                Shield::abort('404/' . $path_canon);
            }
            Lot::set([
                'pager' => new Elevator($files, $chunk, $i, $url . '/' . $path_canon, $elevator, 'pages'),
                'pages' => $pages
            ]);
            return Shield::attach('pages/' . $path_canon);
        }
        // Redirect to parent page if user tries to access the placeholder page…
        if ($name === '$' && File::exist($folder . '.' . $page->state)) {
            Message::info('kick', '<code>' . $url->current . '</code>');
            Guardian::kick($_path);
        }
        Shield::attach('page/' . $path_canon);
    }
    Shield::abort('404/' . $path_canon);
}, 20);
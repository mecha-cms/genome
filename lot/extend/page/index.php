<?php

// Require the plug manually…
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';

// Store page state to registry…
if ($state = Extend::state('page')) {
    Config::extend($state);
}

$path = $url->path;
$folder = PAGE . DS . $path;

$site->is = '404'; // default is `404`
$site->state = 'page'; // default is `page`

if (!$path || $path === $site->path) {
    $site->is = ""; // home page type is ``
} else if ($file = File::exist([
    $folder . '.page',
    $folder . '.archive',
    $folder . DS . '$.page',
    $folder . DS . '$.archive'
])) {
    $site->is = 'page';
    $site->state = Path::X($file);
    if (!File::exist($folder . DS . '$.page') && Get::pages($folder, 'page')) {
        $site->is = 'pages';
    }
}

function fn_page_url($content, $lot = []) {
    if (!isset($lot['path'])) {
        return $content;
    }
    global $url;
    $s = Path::F($lot['path'], PAGE);
    return rtrim($url . '/' . ltrim(To::url($s), '/'), '/');
}

Hook::set('page.url', 'fn_page_url', 1);

Lot::set([
    'message' => Message::get(),
    'page' => new Page,
    'pager' => new Elevator([], 1, 0, true, [], $site->is),
    'pages' => [],
    'parent' => new Page,
    'token' => Guardian::token()
]);

Route::set(['%*%/%i%', '%*%', ""], function($path = "", $step = null) use($state) {
    // Include global variable(s)…
    extract(Lot::get(null, []));
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = str_replace('../', "", urldecode($path));
    $path_f = ltrim($path === "" ? $site->path : $path, '/');
    Config::set('step', $step); // 1–based index…
    if ($step === 1 && !$url->query && $path === $site->path) {
        Message::info('kick', '<code>' . $url->current . '</code>');
        Guardian::kick(""); // Redirect to home page…
    }
    $folder = rtrim(PAGE . DS . To::path($path_f), DS);
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
    if ($file = File::exist([
        // Check for page that has numeric file name…
        $folder . DS . $h . '.page', // `lot\page\page-slug\1.page`
        $folder . DS . $h . '.archive', // `lot\page\page-slug\1.archive`
        $folder . DS . $h . DS . '$.page', // `lot\page\page-slug\1\$.page`
        $folder . DS . $h . DS . '$.archive', // `lot\page\page-slug\1\$.archive`
        // Else…
        $folder . '.page', // `lot\page\page-slug.page`
        $folder . '.archive', // `lot\page\page-slug.archive`
        $folder . DS . '$.page', // `lot\page\page-slug\$.page`
        $folder . DS . '$.archive' // `lot\page\page-slug\$.archive`
    ])) { // File does exist, then …
        if ($path !== "") {
            $site->is = 'page';
        }
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
        $folder_parent = Path::D($folder);
        $path_parent = Path::D($path);
        $name_parent = Path::B($folder_parent);
        if ($file_parent = File::exist([
            $folder_parent . '.page', // `lot\page\parent-slug.page`
            $folder_parent . '.archive', // `lot\page\parent-slug.archive`
            $folder_parent . DS . '$.page', // `lot\page\parent-slug\$.page`
            $folder_parent . DS . '$.archive' // `lot\page\parent-slug\$.archive`
        ])) {
            $page_parent = new Page($file_parent);
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $page_parent->sort($sort);
            $chunk = $page_parent->chunk($chunk);
            $files_parent = Get::pages($folder_parent, 'page', $sort, 'slug');
        } else {
            $files_parent = [];
        }
        Lot::set([
            'page' => $page,
            'pager' => new Elevator($files_parent, null, $page->slug, $url . '/' . $path_parent, $elevator, $site->is),
            'parent' => new Page($file_parent)
        ]);
        Config::set('page.title', new Anemon([$page->title, $site->title], ' &#x00B7; '));
        $x = '.' . $page->state;
        if (!File::exist([
            $folder . DS . '$' . $x, // `lot\page\page-slug\$.{page,archive}`
            $folder . DS . $h . DS . '$' . $x // `lot\page\page-slug\1\$.{page,archive}`
        ])) {
            if (
                $step !== null &&
                File::exist($folder . DS . $step . $x) // `lot\page\page-slug\1.{page,archive}`
            ) {
            // Has page with numeric file name!
            } else if ($files = Get::pages($folder, 'page', $sort, 'path')) {
                if ($query = l(Request::get($config->q, ""))) {
                    Config::set('page.title', new Anemon([$language->search . ': ' . $query, $page->title, $site->title], ' &#x00B7; '));
                    $query = explode(' ', $query);
                    Config::set('search', new Page(null, ['query' => $query], ['*', 'search']));
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
                foreach (Anemon::eat($files)->chunk($chunk, $i) as $file) {
                    $pages[] = new Page($file);
                }
                if (empty($pages)) {
                    // Greater than the maximum step or less than `1`, abort!
                    $site->is = '404';
                    Shield::abort('404/' . $path_f);
                }
                if ($path !== "") {
                    $site->is = 'pages';
                    $site->page = $page;
                    foreach ((array) $state['page'] as $k => $v) {
                        $site->page->{'_' . $k} = $v;
                    }
                }
                Lot::set([
                    'pager' => new Elevator($files, $chunk, $i, $url . '/' . $path_f, $elevator, $site->is),
                    'pages' => $pages
                ]);
                Shield::attach('pages/' . $path_f);
            // Redirect to parent page if user tries to access the placeholder page…
            } else if ($name === '$' && File::exist($folder . $x)) {
                Message::info('kick', '<code>' . $url->current . '</code>');
                Guardian::kick($path_parent);
            }
        }
        Shield::attach('page/' . $path_f);
    }
}, 20);
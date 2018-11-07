<?php namespace fn\page;

// Require the plug manually…
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';

// Include worker(s)…
require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'config.php';

$state = \Extend::state('page');

function url($url = "", array $lot = []) {
    if (!$path = $this->path) {
        return $url;
    }
    $path = \Path::F($path, PAGE, '/');
    return rtrim($GLOBALS['URL']['$'] . '/' . ltrim($path, '/'), '/');
}

\Hook::set('page.url', __NAMESPACE__ . "\\url", 2);

\Lot::set([
    'page' => new \Page,
    'pager' => new \Pager([], [], true),
    'pages' => new \Anemon,
    'parent' => new \Page
]);

\Route::set(['%*%/%i%', '%*%', ""], function(string $path = "", $step = null) use($state) {
    // Include global variable(s)…
    extract(\Lot::get(null, []));
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = str_replace('../', "", urldecode($path));
    $path_canon = ltrim($path === "" ? $site->path : $path, '/');
    if ($step === 1 && !$url->query && $path === $site->path) {
        \Message::info('kick', '<code>' . $url->current . '</code>');
        \Guardian::kick(""); // Redirect to home page…
    }
    $folder = rtrim(PAGE . DS . \To::path($path_canon), DS);
    $name = \Path::B($folder);
    $i = ($h = $step ?: 1) - 1; // 0-based index…
    \Config::set('trace', new \Anemon([$site->title], ' &#x00B7; '));
    if ($file = $site->is('page')) {
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $k = PAGE;
        $sort = $site->page('sort', [1, 'path']);
        $chunk = $site->page('chunk', 5);
        foreach (explode('/', '/' . $path) as $v) {
            $k .= $v ? DS . $v : "";
            if ($f = \File::exist([
                $k . '.page',
                $k . '.archive'
            ])) {
                $f = new \Page($f);
                $sort = $f->sort ?: $sort;
                $chunk = $f->chunk ?: $chunk;
            }
            if ($fn = \File::exist($k . DS . 'index.php')) include $fn;
        }
        $page = new \Page($file);
        // Create pager for single page mode
        $parent_folder = \Path::D($folder);
        $parent_path = \Path::D($path);
        if ($parent_file = \File::exist([
            $parent_folder . '.page', // `lot\page\parent-slug.page`
            $parent_folder . '.archive', // `lot\page\parent-slug.archive`
            $parent_folder . DS . '$.page', // `lot\page\parent-slug\$.page`
            $parent_folder . DS . '$.archive' // `lot\page\parent-slug\$.archive`
        ])) {
            $parent_page = new \Page($parent_file);
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $parent_page->sort ?: $sort;
            $chunk = $parent_page->chunk ?: $chunk;
            $parent_files = \Get::pages($parent_folder, 'page', $sort, 'slug')->vomit();
        } else {
            $parent_files = [];
        }
        $pager = new \Pager($parent_files, $page->slug, $url . '/' . $parent_path);
        $pager_previous = $pager->config['direction']['<'];
        $pager_next = $pager->config['direction']['>'];
        \Lot::set([
            'page' => $page,
            'pager' => $pager,
            'parent' => $parent_page ?? new \Page
        ]);
        \Config::set('trace', new \Anemon([$page->title, $site->title], ' &#x00B7; '));
        \Config::set('has', [
            $pager_previous => !!$pager->{$pager_previous},
            $pager_next => !!$pager->{$pager_next}
        ]);
        if (!$site->is('pages')) {
            // Page(s) view has been disabled!
        } else {
            $pages = \Get::pages($folder, 'page', $sort, 'path');
            if ($query = \l(\HTTP::get($site->q, ""))) {
                \Config::set('is.search', true);
                \Config::set('trace', new \Anemon([$language->search . ': ' . $query, $page->title, $site->title], ' &#x00B7; '));
                $query = explode(' ', $query);
                $pages = $pages->is(function($v) use($query) {
                    $v = str_replace('-', "", \Path::N($v));
                    foreach ($query as $q) {
                        if (strpos($v, $q) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            $pager = new \Pager($pages->vomit(), [$chunk, $i], $url . '/' . $path_canon);
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                \Config::set('is.error', 404);
                \Config::set('has', [
                    $pager->config['direction']['<'] => false,
                    $pager->config['direction']['>'] => false
                ]);
                return \Shield::abort('404/' . $path_canon);
            } else {
                \Lot::set([
                    'pager' => $pager,
                    'pages' => $pages
                ]);
                \Config::set('has', [
                    $pager_previous => !!$pager->{$pager_previous},
                    $pager_next => !!$pager->{$pager_next}
                ]);
                return \Shield::attach('pages/' . $path_canon);
            }
        }
        // Redirect to parent page if user tries to access the placeholder page…
        if ($name === '$' && \File::exist($folder . '.' . $page->state)) {
            \Message::info('kick', '<code>' . $url->current . '</code>');
            \Guardian::kick($parent_path);
        }
        return \Shield::attach('page/' . $path_canon);
    }
    return \Shield::abort('404/' . $path_canon);
}, 20);
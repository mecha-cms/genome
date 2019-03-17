<?php namespace fn\route;

function page(string $path = null, $step = null) {
    // Load default page state(s)…
    $state = \Extend::state('page');
    // Include global variable(s)…
    extract(\Lot::get(), \EXTR_SKIP);
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = \str_replace('../', "", \urldecode($path));
    $path_default = \rtrim($path === "" ? $state['path'] : $path, '/');
    if ($step < 2 && $path === $state['path'] && !$url->query) {
        \Message::info('kick', '<code>' . $url->current . '</code>');
        \Guard::kick(""); // Redirect to home page…
    }
    $folder = \rtrim(PAGE . DS . strtr($path_default, '/', DS), DS);
    $name = \Path::B($folder);
    $i = ($h = $step ?? 1) - 1; // 0-based index…
    // Set default site title
    \Config::set('trace', new \Anemon([$config->title], ' &#x00B7; '));
    if ($file = \File::exist([
        // `.\lot\page\page-slug.{page,archive}`
        $folder . '.page',
        $folder . '.archive',
        // `.\lot\page\page-slug\$.{page,archive}`
        $folder . DS . '$.page',
        $folder . DS . '$.archive',
        // `.\lot\page\page-slug\1.{page,archive}`
        $folder . DS . $h . '.page',
        $folder . DS . $h . '.archive',
        // `.\lot\page\page-slug\1\$.{page,archive}`
        $folder . DS . $h . DS . '$.page',
        $folder . DS . $h . DS . '$.archive'
    ])) {
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $k = PAGE;
        $page = new \Page($file);
        $sort = $page->sort ?? $config->page('sort') ?? [1, 'path'];
        $chunk = $page->chunk ?? $config->page('chunk') ?? 5;
        foreach (\explode('/', '/' . $path) as $v) {
            $k .= $v ? DS . $v : "";
            if ($f = \File::exist([
                $k . '.page',
                $k . '.archive'
            ])) {
                $f = new \Page($f);
                $sort = $f->sort ?? $sort;
                $chunk = $f->chunk ?? $chunk;
            }
            if (\is_file($fn = $k . DS . 'index.php')) {
                require $fn;
            }
        }
        // Create pager for single page mode
        $parent_folder = \Path::D($folder);
        $parent_path = \Path::D($path);
        if ($parent_file = \File::exist([
            $parent_folder . '.page', // `.\lot\page\parent-slug.page`
            $parent_folder . '.archive', // .\`lot\page\parent-slug.archive`
            $parent_folder . DS . '$.page', // `.\lot\page\parent-slug\$.page`
            $parent_folder . DS . '$.archive' // `.\lot\page\parent-slug\$.archive`
        ])) {
            $parent_page = new \Page($parent_file);
            $parent_slugs = \Get::pages($parent_folder, 'page', $sort, 'slug')->vomit();
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $parent_page->sort ?? $sort;
            $chunk = $parent_page->chunk ?? $chunk;
        }
        // Inherit page’s `sort` and `chunk` property
        \Config::set('sort', $sort);
        \Config::set('chunk', $chunk);
        $pager = new \Pager\Page($parent_slugs ?? [], $page->slug, $url . '/' . $parent_path);
        \Lot::set([
            'page' => $page,
            'pager' => $pager,
            'parent' => $parent_page ?? new \Page
        ]);
        \Config::set('trace', new \Anemon([$page->title, $config->title], ' &#x00B7; '));
        \Config::set('has', [
            'next' => !!$pager->next,
            'parent' => !!$pager->parent,
            'prev' => !!$pager->prev
        ]);
        if (\Config::get('is.page')) {
            // Page(s) view has been disabled!
        } else {
            $pages = \Get::pages($folder, 'page', $sort, 'path');
            if ($query = \l(\HTTP::get($config->q) ?? "")) {
                \Config::set('is.search', true);
                \Config::set('trace', new \Anemon([$language->search . ': ' . $query, $page->title, $config->title], ' &#x00B7; '));
                $query = \explode(' ', $query);
                $pages = $pages->is(function($v) use($query) {
                    $v = \str_replace('-', "", \Path::N($v));
                    foreach ($query as $q) {
                        if (\strpos($v, $q) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            $pager = new \Pager\Pages($pages->vomit(), [$chunk, $i], $url . '/' . $path_default);
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                \Config::set('is.error', 404);
                \Config::set('has', [
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ]);
                return \Shield::abort('404/' . $path_default . '/' . $h);
            } else {
                \Lot::set([
                    'pager' => $pager,
                    'pages' => $pages
                ]);
                \Config::set('has', [
                    'next' => !!$pager->next,
                    'parent' => !!$pager->parent,
                    'prev' => !!$pager->prev,
                ]);
                return \Shield::attach('pages/' . $path_default);
            }
        }
        // Redirect to parent page if user tries to access the placeholder page…
        if ($name === '$' && \is_file($folder . '.' . $page->x)) {
            \Message::info('kick', '<code>' . $url->current . '</code>');
            \Guard::kick($parent_path);
        }
        return \Shield::attach('page/' . $path_default . '/' . $h);
    }
    return \Shield::abort('404/' . $path_default . '/' . $h);
}

\Lot::set([
    'page' => new \Page,
    'pager' => new \Pager\Pages([], [5, 0], $GLOBALS['URL']['$']),
    'pages' => new \Anemon,
    'parent' => new \Page
]);

\Route::set(['(.+)/(\d+)', '(.+)', ""], __NAMESPACE__ . "\\page", 20);
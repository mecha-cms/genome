<?php namespace _\route;

function page($form) {
    global $config, $language, $url;
    $current = $url->i;
    // Load default page state(s)…
    $state = \extend('page');
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = \str_replace('../', "", \urldecode($this[0]));
    $default = \rtrim($path === "" ? $state['path'] : $path, '/');
    if ($current < 2 && $path === $state['path'] && !$url->query) {
        \Guard::kick(""); // Redirect to home page…
    }
    $folder = \rtrim(PAGE . DS . strtr($default, '/', DS), DS);
    $name = \Path::B($folder);
    $i = ($h = $current ?? 1) - 1; // 0-based index…
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
            // Load user function(s) from the current page folder if any,
            // stacked from the parent page(s)
            if (\is_file($fn = $k . DS . 'index.php')) {
                call_user_func(function() use($fn) {
                    extract($GLOBALS, EXTR_SKIP);
                    require $fn;
                });
            }
        }
        // Create pager for single page mode
        $parent_folder = \Path::D($folder);
        $parent_path = \Path::D($path);
        if ($parent_file = \File::exist([
            $parent_folder . '.page', // `.\lot\page\parent-slug.page`
            $parent_folder . '.archive', // `.\lot\page\parent-slug.archive`
            $parent_folder . DS . '$.page', // `.\lot\page\parent-slug\$.page`
            $parent_folder . DS . '$.archive' // `.\lot\page\parent-slug\$.archive`
        ])) {
            $parent_page = new \Page($parent_file);
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $parent_page->sort ?? $sort;
            $chunk = $parent_page->chunk ?? $chunk;
            $parent_slugs = \Get::pages($parent_folder, 'page', $sort, 'slug')->get();
        }
        $pager = new \Pager\Page($parent_slugs ?? [], $page->slug, $url . '/' . $parent_path);
        $GLOBALS['page'] = $page;
        $GLOBALS['pager'] = $pager;
        $GLOBALS['parent'] = $parent_page ?? new \Page;
        $GLOBALS['t'][] = $page->title;
        \Config::set([
            'chunk' => $chunk, // Inherit page’s `chunk` property
            'has' => [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ],
            'sort' => $sort // Inherit page’s `sort` property
        ]);
        if (\Config::get('is.page')) {
            // Page(s) view has been disabled!
        } else {
            $pages = \Get::pages($folder, 'page', $sort, 'path');
            if ($query = \l($form[$config->q] ?? "")) {
                $GLOBALS['t'][] = $language->doSearch . ': ' . $query;
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
                \Config::set('is.search', true);
            }
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], $url . '/' . $default);
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
                $GLOBALS['t'][] = $language->isError;
                $this->view('404/' . $default . '/' . $h);
            } else {
                \Config::set('has', [
                    'next' => !!$pager->next,
                    'parent' => !!$pager->parent,
                    'prev' => !!$pager->prev,
                ]);
                $GLOBALS['page'] = $page;
                $GLOBALS['pager'] = $pager;
                $GLOBALS['pages'] = $pages;
                $this->status(200);
                $this->view('pages/' . $default);
            }
        }
        // Redirect to parent page if user tries to access the placeholder page…
        if ($name === '$' && \is_file($folder . '.' . $page->x)) {
            Guard::kick($parent_path);
        }
        $this->status(200);
        $this->view('page/' . $default . '/' . $h);
    }
    Config::set('is.error', 404);
    $GLOBALS['t'][] = $language->isError;
    $this->view('404/' . $default . '/' . $h);
}

$GLOBALS['page'] = new \Page;
$GLOBALS['pager'] = new \Pager\Pages;
$GLOBALS['pages'] = new \Anemon;
$GLOBALS['parent'] = new \Page;
$GLOBALS['t'] = new \Anemon([$config->title], ' &#x00B7; ');

\Route::set(['*', ""], __NAMESPACE__ . "\\page", 20);
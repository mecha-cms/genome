<?php namespace _\lot\x\page;

$GLOBALS['page'] = new \Page;
$GLOBALS['pager'] = new \Pager\Pages;
$GLOBALS['pages'] = new \Pages;
$GLOBALS['parent'] = new \Page;

function route() {
    global $config, $language, $url;
    $i = ($url->i ?? 1) - 1;
    // Load default page state(s)…
    $state = \state('page');
    // Prevent directory traversal attack
    // <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = \str_replace('../', "", \urldecode($this[0]));
    if ($i < 1 && $path === $state['/'] && !$url->query) {
        \Guard::kick(""); // Redirect to home page
    }
    // Default home page path
    $p = \trim($path === "" ? $state['/'] : $path, '/');
    $folder = \rtrim(PAGE . DS . \strtr($p, '/', DS), DS);
    if ($file = \File::exist([
        $folder . '.page',
        $folder . '.archive'
    ])) {
        $k = PAGE;
        $page = new \Page($file);
        $sort = $page['sort'] ?? [1, 'path'];
        $chunk = $page['chunk'] ?? 5;
        foreach (\explode('/', '/' . $path) as $v) {
            $k .= $v ? DS . $v : "";
            if ($f = \File::exist([
                $k . '.page',
                $k . '.archive'
            ])) {
                $f = new \Page($f);
                $sort = $f['sort'] ?? $sort;
                $chunk = $f['chunk'] ?? $chunk;
            }
            // Load user function(s) from the current page folder if any,
            // stacked from the parent page(s)
            if (\is_file($fn = $k . DS . 'index.php')) {
                \call_user_func(function() use($fn) {
                    extract($GLOBALS, \EXTR_SKIP);
                    require $fn;
                });
            }
        }
        $parent_path = \Path::D($path);
        $parent_folder = \Path::D($folder);
        if ($parent_file = \File::exist([
            $parent_folder . '.page', // `.\lot\page\parent-name.page`
            $parent_folder . '.archive', // `.\lot\page\parent-name.archive`
            $parent_folder . DS . '.page', // `.\lot\page\parent-name\.page`
            $parent_folder . DS . '.archive' // `.\lot\page\parent-name\.archive`
        ])) {
            $parent_page = new \Page($parent_file);
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $parent_page['sort'] ?? $sort;
            $chunk = $parent_page['chunk'] ?? $chunk;
            $parent_pages = \map(\Pages::from($parent_folder, 'page')->sort($sort), function($v) {
                return $v->name;
            });
        }
        $pager = new \Pager\Page($parent_pages ?? [], $page->name, $url . '/' . $parent_path);
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
        $pages = \Pages::from($folder, 'page')->sort($sort);
        // No page(s) means “page” mode
        if ($pages->count() === 0 || \is_file($folder . DS . '.' . $page->x)) {
            $this->content('page/' . $p . '/' . ($i + 1));
        }
        // Create pager for “pages” mode
        $pager = new \Pager\Pages($pages->get(), [$chunk, $i], $url . '/' . $p);
        $pages = $pages->chunk($chunk, $i);
        if ($pages->count() > 0) {
            \Config::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev,
            ]);
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            $this->content('pages/' . $p . '/' . ($i + 1));
        }
    }
    \Config::set([
        'has' => [
            'i' => false,
            'next' => false,
            'parent' => false,
            'prev' => false
        ],
        'is' => ['error' => 404]
    ]);
    $GLOBALS['t'][] = $language->isError;
    $this->status(404);
    $this->content('404/' . $p . '/' . ($i + 1));
}

\Route::set(['*', ""], __NAMESPACE__ . "\\route", 20);
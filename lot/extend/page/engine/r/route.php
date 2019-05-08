<?php namespace _\route;

function page() {
    $current = $this->url->i;
    // Load default page state(s)…
    $state = \Extend::state('page');
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = \str_replace('../', "", \urldecode($this[0]));
    $default = \rtrim($path === "" ? $state['path'] : $path, '/');
    if ($current < 2 && $path === $state['path'] && !$this->url->query) {
        $this->message::info('kick', '<code>' . $this->url->current . '</code>');
        $this->kick(""); // Redirect to home page…
    }
    $folder = \rtrim(PAGE . DS . strtr($default, '/', DS), DS);
    $name = \Path::B($folder);
    $i = ($h = $current ?? 1) - 1; // 0-based index…
    // Set default site title
    $this->title($this->config->title);
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
        $sort = $page->sort ?? $this->config->page('sort') ?? [1, 'path'];
        $chunk = $page->chunk ?? $this->config->page('chunk') ?? 5;
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
        $pager = new \Pager\Page($parent_slugs ?? [], $page->slug, $this->u . '/' . $parent_path);
        $GLOBALS['page'] = $page;
        $GLOBALS['pager'] = $pager;
        $GLOBALS['parent'] = $parent_page ?? new \Page;
        $this->config::set('sort', $sort); // Inherit page’s `sort` property
        $this->config::set('chunk', $chunk); // Inherit page’s `chunk` property
        $this->title([$page->title, $this->config->title]);
        $this->config::set('has', [
            'next' => !!$pager->next,
            'parent' => !!$pager->parent,
            'prev' => !!$pager->prev
        ]);
        if ($this->config::get('is.page')) {
            // Page(s) view has been disabled!
        } else {
            $pages = \Get::pages($folder, 'page', $sort, 'path');
            if ($query = \l(\HTTP::get($this->config->q) ?? "")) {
                $this->title([$this->language->search . ': ' . $query, $page->title, $this->config->title]);
                $this->config::set('is.search', true);
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
            $pager = new \Pager\Pages($pages->vomit(), [$chunk, $i], $this->u . '/' . $default);
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                $this->config::set('is.error', 404);
                $this->config::set('has', [
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ]);
                $this->view('404/' . $default . '/' . $h);
            } else {
                $this->config::set('has', [
                    'next' => !!$pager->next,
                    'parent' => !!$pager->parent,
                    'prev' => !!$pager->prev,
                ]);
                $GLOBALS['pager'] = $pager;
                $GLOBALS['pages'] = $pages;
                $this->view('pages/' . $default);
            }
        }
        // Redirect to parent page if user tries to access the placeholder page…
        if ($name === '$' && \is_file($folder . '.' . $page->x)) {
            $this->message::info('kick', '<code>' . $this->url->current . '</code>');
            $this->kick($parent_path);
        }
        $this->view('page/' . $default . '/' . $h);
    }
    $this->view('404/' . $default . '/' . $h);
}

$GLOBALS['page'] = new \Page;
$GLOBALS['pager'] = new \Pager\Pages([], [5, 0], $GLOBALS['URL']['$']);
$GLOBALS['pages'] = new \Anemon;
$GLOBALS['parent'] = new \Page;

\Route::set(['*', ""], __NAMESPACE__ . "\\page", 20);
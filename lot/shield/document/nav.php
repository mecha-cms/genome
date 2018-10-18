<ul><!--
  --><li>
    <?php if ($site->is('home')): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li><!--
  <?php if ($menus = Get::pages(PAGE, 'page', [1, 'slug'])): ?>
    <?php foreach ($menus as $menu): ?>
    <?php $slug = $menu['slug']; ?>
    <?php if ($slug === $site->path) continue; ?>
    <?php

    $p = Page::open($menu['path'])->get([
        'url' => null,
        'title' => To::title($slug),
        'link' => null
    ]);

    ?>
    --><li>
      <?php if ($slug === $url->path || strpos($url->path . '/', $slug . '/') === 0): ?>
      <span><?php echo $p['title']; ?></span>
      <?php else: ?>
      <a href="<?php echo $p['link'] ?: $p['url']; ?>"><?php echo $p['title']; ?></a>
      <?php endif; ?>
    </li><!--
    <?php endforeach; ?>
  <?php endif; ?>
--></ul>
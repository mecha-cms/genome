<ul><!--
  --><li>
    <?php if ($site->is('home')): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li><!--
  <?php if ($menus = Get::pages(PAGE, 'page', [1, 'slug'])->vomit()): ?>
    <?php foreach ($menus as $menu): ?>
    <?php if ($menu === $site->path) continue; ?>
    <?php

    $p = Page::open(PAGE . DS . $menu . '.page')->get([
        'url' => null,
        'title' => To::title($menu),
        'link' => null
    ]);

    ?>
    --><li>
      <?php if ($menu === $url->path || strpos($url->path . '/', $menu . '/') === 0): ?>
      <span><?php echo $p['title']; ?></span>
      <?php else: ?>
      <a href="<?php echo $p['link'] ?: $p['url']; ?>"><?php echo $p['title']; ?></a>
      <?php endif; ?>
    </li><!--
    <?php endforeach; ?>
  <?php endif; ?>
--></ul>
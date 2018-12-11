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

    $m = Page::open(PAGE . DS . $menu . '.page')->get([
        'link' => null,
        'title' => To::title($menu),
        'url' => null
    ]);

    ?>
    --><li>
      <?php if ($menu === $url->path || strpos($url->path . '/', $menu . '/') === 0): ?>
      <span><?php echo $m['title']; ?></span>
      <?php else: ?>
      <a href="<?php echo $m['link'] ?: $m['url']; ?>"><?php echo $m['title']; ?></a>
      <?php endif; ?>
    </li><!--
    <?php endforeach; ?>
  <?php endif; ?>
--></ul>
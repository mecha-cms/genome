<ul><!--
  --><li>
    <?php if (!$url->path || $url->path === $site->slug): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li><!--
  <?php if ($menus = Get::pages(PAGE, 'page', 1, 'slug', 'slug')): ?>
    <?php foreach ($menus as $menu): ?>
    <?php if ($menu === $site->slug) continue; ?>
    <?php $t = Page::open(PAGE . DS . $menu . '.page')->get('title', To::title($menu)); ?>
    --><li>
      <?php if ($url->path === $menu || strpos('/' . $url->path . '/', '/' . $menu . '/') === 0): ?>
      <span><?php echo $t; ?></span>
      <?php else: ?>
      <a href="<?php echo $url . '/' . $menu; ?>"><?php echo $t; ?></a>
      <?php endif; ?>
    </li><!--
    <?php endforeach; ?>
  <?php endif; ?>
--></ul>
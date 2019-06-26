<ul>
  <li>
    <?php if ($site->is('home')): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li>
  <?php foreach ($menus as $menu): ?>
  <li>
    <?php if ($menu->active): ?>
    <span><?php echo $menu->title; ?></span>
    <?php else: ?>
    <a href="<?php echo $menu->link ?: $menu->url; ?>"><?php echo $menu->title; ?></a>
    <?php endif; ?>
  </li>
  <?php endforeach; ?>
</ul>
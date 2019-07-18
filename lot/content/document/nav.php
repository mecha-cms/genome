<ul>
  <li>
    <?php if ($site->is('home')): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li>
  <?php foreach ($links as $link): ?>
  <li>
    <?php if ($link->active): ?>
    <span><?php echo $link->title; ?></span>
    <?php else: ?>
    <a href="<?php echo $link->link ?: $link->url; ?>"><?php echo $link->title; ?></a>
    <?php endif; ?>
  </li>
  <?php endforeach; ?>
</ul>
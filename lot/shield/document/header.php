<header>
  <h1>
    <?php if (!$url->path || $url->path === $site->slug): ?>
    <span><?php echo $site->title; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $site->title; ?></a>
    <?php endif; ?>
  </h1>
  <p><?php echo $site->description; ?></p>
  <nav><?php Shield::get('menu'); ?></nav>
</header>
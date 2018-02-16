<?php get('header'); ?>
<main>
  <?php echo $message; ?>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h2><span><?php echo $page->title; ?></span></h2>
      <p><strong><?php echo $language->author; ?>:</strong> <?php echo $page->author; ?></p>
    </header>
    <div>
      <?php echo $page->content; ?>
      <?php if ($page->link): ?>
      <p><a href="<?php echo $page->link; ?>" rel="nofollow" target="_blank"><?php echo $language->link; ?> &#x21E2;</a></p>
      <?php endif; ?>
    </div>
    <footer>
      <?php $update = new Date($page->update); ?>
      <p><strong><?php echo $language->updateed; ?>:</strong> <time datetime="<?php echo $update->W3C; ?>"><?php echo $update->{str_replace('-', '_', $site->language)}; ?></time></p>
    </footer>
  </article>
</main>
<?php if (strpos($url->path, '/') !== false): ?>
<nav><?php echo $pager; ?></nav>
<?php endif; ?>
<?php get('footer'); ?>
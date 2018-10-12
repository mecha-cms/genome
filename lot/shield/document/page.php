<?php Shield::get('header'); ?>
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
      <p><strong><?php echo $language->updateed; ?>:</strong> <time datetime="<?php echo $page->update->W3C; ?>"><?php echo $page->update->{str_replace('-', '_', $site->language)}; ?></time></p>
    </footer>
  </article><?php Shield::get('comments'); ?>
</main>
<?php if ($site->has('parent')): ?>
<nav><?php echo $pager; ?></nav>
<?php endif; ?>
<?php Shield::get('footer'); ?>
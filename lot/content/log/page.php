<?php static::before(); ?>
<main>
  <article id="page-<?php echo $page->id; ?>">
    <h2><span><?php echo $page->title; ?></span></h2>
    <?php if ($site->has('parent')): ?>
    <p><time datetime="<?php echo $page->time->ISO8601; ?>"><?php echo $page->time->{r('-', '_', $site->language)}; ?></time></p>
    <?php endif; ?>
    <?php echo $page->content; ?>
    <?php if ($page->link): ?>
    <p><a href="<?php echo $page->link; ?>" rel="nofollow" target="_blank"><?php echo $language->link; ?> &#x21E2;</a></p>
    <?php endif; ?>
  </article><?php static::comments(); ?>
</main>
<?php if ($site->has('page') && $site->has('parent')): ?>
<nav><?php echo $pager; ?></nav>
<?php endif; ?>
<?php static::after(); ?>
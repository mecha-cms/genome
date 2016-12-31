<?php Shield::get('header'); ?>
<main>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h2><span><?php echo $page->title; ?></span></h2>
      <?php $update = new Date($page->update); ?>
      <p>
        <strong><?php echo $language->author; ?>:</strong> <?php echo $page->author; ?>
        &#x00B7;
        <time datetime="<?php echo $update->W3C; ?>"><strong><?php echo $language->update; ?>:</strong> <?php echo $update->{str_replace('-', '_', $config->language)}; ?></time>
      </p>
    </header>
    <section>
      <blockquote><?php echo $page->description; ?></blockquote>
      <?php echo $page->content; ?>
    </section>
  </article>
</main>
<?php Shield::get('footer'); ?>
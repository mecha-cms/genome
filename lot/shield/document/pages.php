<?php Shield::get('header'); ?>
<main>
  <?php foreach ($pages as $page): ?>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h3><a href="<?php echo $page->link ?: $page->url; ?>"><?php echo $page->title; ?></a></h3>
    </header>
    <section><?php echo $page->description; ?></section>
    <footer></footer>
  </article>
  <?php endforeach; ?>
  <nav><?php echo $pager; ?></nav>
</main>
<?php Shield::get('footer'); ?>
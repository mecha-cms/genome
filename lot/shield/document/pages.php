<?php include 'header.php'; ?>
<main>
  <?php if ($pages): ?>
  <?php foreach ($pages as $page): ?>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h3><a href="<?php echo $page->link ?: $page->url; ?>"><?php echo $page->title; ?></a></h3>
    </header>
    <section>
      <p><?php echo $page->description; ?></p>
    </section>
    <footer></footer>
  </article>
  <?php endforeach; ?>
  <p><?php echo $pager; ?></p>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
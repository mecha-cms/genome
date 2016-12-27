<?php include 'header.php'; ?>
<main>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h2><span><?php echo $page->title; ?></span></h2>
      <?php $date = new Date($page->update); ?>
      <p><time datetime="<?php echo $date->W3C; ?>"><strong><?php echo $language->update; ?>:</strong> <?php echo $date->F1; ?></time></p>
    </header>
    <section>
      <blockquote>
        <p><?php echo $page->description; ?></p>
      </blockquote>
      <?php echo $page->content; ?>
    </section>
    <footer>
      <p><strong>Author:</strong> <?php echo $page->author; ?></p>
    </footer>
  </article>
</main>
<?php include 'footer.php'; ?>
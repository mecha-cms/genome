<?php include 'header.php'; ?>
<main>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h2><span><?php echo $page->title; ?></span></h2>
      <p><time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->F1; ?></time></p>
    </header>
    <section><?php echo $page->content; ?></section>
    <footer>
      <p><strong>Author:</strong> <?php echo $page->author; ?></p>
    </footer>
  </article>
</main>
<?php include 'footer.php'; ?>
<?php include 'header.php'; ?>
<main class="posts">
  <?php if ($posts): ?>
  <?php foreach ($posts as $post): ?>
  <article class="post" id="post-<?php echo $post->id; ?>">
    <header class="post-header">
      <h3 class="post-title"><?php echo $post->title; ?></h3>
      <time datetime="<?php echo $post->date->W3C; ?>">
        <?php echo $post->date->FORMAT_1; ?>
      </time>
    </header>
    <div class="post-content">
      <?php echo $post->content; ?>
    </div>
    <footer class="post-footer">
      
    </footer>
  </article>
  <?php endforeach; ?>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
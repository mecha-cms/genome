<!DOCTYPE html>
<html class>
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <title><?php echo $site->trace; ?></title>
  </head>
  <body>
    <header>
      <h1>
        <a href="<?php echo $url; ?>">
          <?php echo $site->title; ?>
        </a>
      </h1>
      <p><?php echo $site->description; ?></p>
    </header>
    <main>
      <h2><?php echo $page->title; ?></h2>
      <?php echo $page->description; ?>
      <?php foreach ($pages as $page): ?>
      <article>
        <h3>
          <a href="<?php echo $page->url; ?>">
            <?php echo $page->title; ?>
          </a>
        </h3>
      </article>
      <?php endforeach; ?>
      <nav><?php echo $pager; ?></nav>
    </main>
    <footer>
      <p>&#x00A9; <?php echo $date->year; ?></p>
    </footer>
  </body>
</html>
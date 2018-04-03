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
      <article>
        <h2><?php echo $page->title; ?></h2>
        <?php echo $page->content; ?>
      </article>
      <nav><?php echo $pager; ?></nav>
    </main>
    <footer>
      <p>&#x00A9; <?php echo $date->year; ?></p>
    </footer>
  </body>
</html>
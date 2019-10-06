<!DOCTYPE html>
<html class dir="<?= $site->direction; ?>" lang="<?= $site->language; ?>">
  <head>
    <meta charset="<?= $site->charset; ?>">
    <meta content="width=device-width" name="viewport">
    <?php if ($w = w($page->description ?? $site->description ?? "")): ?>
    <meta content="<?= $w; ?>" name="description">
    <?php endif; ?>
    <?php if ($page->x === 'archive'): ?>
    <!-- Prevent search engines from indexing pages with `archive` state -->
    <meta content="noindex" name="robots">
    <?php endif; ?>
    <meta content="<?= $page->author; ?>" name="author">
    <title><?= w($t->reverse); ?></title>
    <link href="<?= $url; ?>/favicon.ico" rel="shortcut icon">
    <link href="<?= $url->clean; ?>" rel="canonical">
  </head>
  <body>
    <?= $alert; ?>
    <div>
      <header>
        <h1>
          <?php if ($site->is('home')): ?>
          <span><?= $site->title; ?></span>
          <?php else: ?>
          <a href="<?= $url; ?>"><?= $site->title; ?></a>
          <?php endif; ?>
        </h1>
        <p><?= $site->description; ?></p>
        <nav><?= self::nav(); ?></nav>
      </header>
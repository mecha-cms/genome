<!DOCTYPE html>
<html class dir="<?php echo $site->direction; ?>" lang="<?php echo $site->language; ?>">
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <meta content="width=device-width" name="viewport">
    <?php if ($w = w($page->description ?? $site->description ?? "")): ?>
    <meta content="<?php echo $w; ?>" name="description">
    <?php endif; ?>
    <?php if ($page->x === 'archive'): ?>
    <!-- Prevent search engines from indexing pages with `archive` state -->
    <meta content="noindex" name="robots">
    <?php endif; ?>
    <meta content="<?php echo $page->author; ?>" name="author">
    <title><?php echo w($t->reverse); ?></title>
    <link href="<?php echo $url; ?>/favicon.ico" rel="shortcut icon">
    <link href="<?php echo $url->clean; ?>" rel="canonical">
  </head>
  <body>
    <header>
      <h1>
        <?php if ($site->is('home')): ?>
        <span><?php echo $site->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $url; ?>"><?php echo $site->title; ?></a>
        <?php endif; ?>
      </h1>
      <p><?php echo $site->description; ?></p>
      <nav><?php static::nav(); ?></nav>
    </header>
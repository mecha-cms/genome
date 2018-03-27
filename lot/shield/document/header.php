<!DOCTYPE html>
<html dir="<?php echo $site->direction; ?>" class>
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <meta name="viewport" content="width=device-width">
    <?php if ($s = To::text($page->description($site->description))): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php endif; ?>
    <?php if ($page->state === 'archive'): ?>
    <!-- Prevent search engines from indexing pages with `archive` state -->
    <meta name="robots" content="noindex">
    <?php endif; ?>
    <meta name="author" content="<?php echo $page->author; ?>">
    <title><?php echo To::text($site->page->title); ?></title>
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
      <nav><?php Shield::get('nav'); ?></nav>
    </header>
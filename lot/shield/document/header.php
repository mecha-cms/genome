<!DOCTYPE html>
<html dir="<?php echo $config->direction; ?>" class="<?php echo $config->type; ?>">
  <head>
    <meta charset="<?php echo $config->charset; ?>">
    <meta name="viewport" content="width=device-width">
    <?php if ($s = To::text($page->description)): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php elseif ($s = To::text($config->description)): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php endif; ?>
    <?php if ($page->state === 'archive'): ?>
    <!-- Prevent search engines from indexing a page with `archive` state -->
    <meta name="robots" content="noindex">
    <?php endif; ?>
    <title><?php echo To::text($config->page->title); ?></title>
    <link href="<?php echo $url; ?>/favicon.ico" rel="shortcut icon">
    <?php $css = File::inspect(SHIELD . '/document/asset/css/document.min.css'); ?>
    <?php $t = $css['__update'] ? '?v=' . $css['__update'] : ""; ?>
    <link href="<?php echo $css['url'] . $t; ?>" rel="stylesheet">
  </head>
  <body>
    <header>
      <h1>
        <?php if ($url->path === "" || $url->path === $config->slug): ?>
        <span><?php echo $config->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $url; ?>"><?php echo $config->title; ?></a>
        <?php endif; ?>
      </h1>
      <p><?php echo $config->description; ?></p>
      <nav><?php Shield::get('menu'); ?></nav>
    </header>
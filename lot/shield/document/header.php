<!DOCTYPE html>
<html dir="<?php echo $config->direction; ?>">
  <head>
    <meta charset="<?php echo $config->charset; ?>">
    <meta name="viewport" content="width=device-width">
    <title><?php echo To::text($config->page->title); ?></title>
    <?php $css = File::inspect(SHIELD . '/document/asset/css/document.min.css'); ?>
    <?php $t = $css['__update'] ? '?v=' . $css['__update'] : ""; ?>
    <link href="<?php echo $css['url'] . $t; ?>" rel="stylesheet">
  </head>
  <body>
    <header>
      <h1>
        <?php if ($url->path === "" || strpos('/' . $url->path . '/', '/' . $config->slug . '/') === 0): ?>
        <span><?php echo $config->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $url; ?>"><?php echo $config->title; ?></a>
        <?php endif; ?>
      </h1>
      <p><?php echo $config->description; ?></p>
      <?php Shield::get('menu'); ?>
    </header>
<!DOCTYPE html>
<html dir="<?php echo $config->direction; ?>">
  <head>
    <meta charset="<?php echo $config->encoding; ?>">
    <meta name="viewport" content="width=device-width">
    <title><?php echo To::text($config->page->title); ?></title>
    <link href="<?php echo $url; ?>/lot/shield/<?php echo $config->shield; ?>/asset/css/<?php echo $config->shield; ?>.min.css" rel="stylesheet">
  </head>
  <body>
    <header>
      <h1>
        <?php if ($url->path === ""): ?>
        <span><?php echo $config->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $url; ?>"><?php echo $config->title; ?></a>
        <?php endif; ?>
      </h1>
      <p><?php echo $config->description; ?></p>
    </header>
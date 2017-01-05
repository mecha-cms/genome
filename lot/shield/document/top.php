<!DOCTYPE html>
<html dir="<?php echo $site->direction; ?>" class="<?php echo $site->type; ?>">
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <meta name="viewport" content="width=device-width">
    <?php if ($s = To::text($page->description)): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php elseif ($s = To::text($site->description)): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php endif; ?>
    <?php if ($page->state === 'archive'): ?>
    <!-- Prevent search engines from indexing a page with `archive` state -->
    <meta name="robots" content="noindex">
    <?php endif; ?>
    <meta name="author" content="<?php echo $page->author; ?>">
    <title><?php echo To::text($site->page->title); ?></title>
    <link href="<?php echo $url; ?>/favicon.ico" rel="shortcut icon">
    <?php $css = File::inspect(SHIELD . '/document/asset/css/document.min.css'); ?>
    <?php $t = $css['__update'] ? '?v=' . $css['__update'] : ""; ?>
    <link href="<?php echo $css['url'] . $t; ?>" rel="stylesheet">
    <?php echo $page->css; ?>
  </head>
  <body>
    <?php Shield::get('header'); ?>
<!DOCTYPE html>
<html class>
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <title><?php echo $site->trace; ?></title>
  </head>
  <body>
    <p><?php echo $language->error . ': ' . $site->is('error'); ?></p>
  </body>
</html>
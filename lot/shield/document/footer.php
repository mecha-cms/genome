    <footer>
      <p><?php Shield::get('path'); ?></p>
      <p>&#x00A9;&#xFE0E; <?php echo $date->year; ?> &#x00B7;&#xFE0E; <a href="<?php echo $url; ?>"><?php echo $config->title; ?></a></p>
    </footer>
    <?php echo $page->js; ?>
  </body>
</html>
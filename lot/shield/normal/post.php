<h1>
<?php echo $post->title; ?>
</h1>
<div>
<?php echo $post->content; ?>
</div>
<footer>
<?php echo $post->title(function($s) {
    return '<mark>' . $s . '</mark>';
}); ?>
</footer>
<p><code><?php echo To::html_encode($post); ?></code></p>
<div><?php _dump_($config()); ?></div>
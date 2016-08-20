<?php include 'header.php'; ?>
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
<?php include 'footer.php'; ?>
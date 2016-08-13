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
<div><?php _dump_(Language::get()); ?></div>

<?php _dump_(o([
    'a' => 'b',
    'c' => 'd',
    'e' => ['f', 'g', 'h', 'i'],
    'j' => [
        0 => 'k',
        1 => 'l',
        '2' => 'm'
    ],
    'n' => [
        0 => 'o',
        1 => 'p',
        'q' => 'r'
    ]
])); ?>
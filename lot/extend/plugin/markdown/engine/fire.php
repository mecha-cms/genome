<?php

r(__DIR__ . DS . 'kernel', [
    'parsedown.php',
    'parsedown-extra.php',
    'parsedown-extra-plugin.php'
]);

$parser = new ParsedownExtraPlugin;

Hook::set('content', function($data, $meta) use($parser) {
	if (!isset($meta['content_type']) || $meta['content_type'] === 'Markdown') {
		return $parser->text($data);
	}
	return $data;
});
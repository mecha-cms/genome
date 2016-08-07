<?php

Sheet::data([
    'title' => 'Test Page',
	'author' => 'Taufik Nurrohman',
	'content_type' => 'HTML',
	'link' => '#',
	'content' => 'Lorem ipsum dolor sit amet.'
], 'content')->saveTo('test.txt');
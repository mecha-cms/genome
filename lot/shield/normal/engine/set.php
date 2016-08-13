<?php

Genome\Sheet::start()->data([
    'title' => 'Test Page',
    'author' => 'Taufik Nurrohman',
    'content' => '<p>Lorem ipsum dolor sit amet.</p>',
    'content_type' => 'HTML'
], 'content')->saveTo(LOT . DS . 'page' . DS . 'test.log');
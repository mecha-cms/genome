<?php

Sheet::data([
    'title' => 'Test Title',
    'author' => 'Taufik Nurrohman',
    'fields' => [
        'foo' => 'bar',
        'baz' => [0, 1, 2, 3, 4, 5, 6, 7]
    ]
], 'content')->saveTo(LOT . DS . 'sheets' . DS . 'my-page.txt');
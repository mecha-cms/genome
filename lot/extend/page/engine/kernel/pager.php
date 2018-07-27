<?php

class Pager extends Elevator {

    public function __construct($input = [], $chunk = [5, 0], $path = true, $config = []) {
        parent::__construct($input, $chunk, $path, array_replace_recursive([
            'direction' => [
                '<' => 'previous',
                '>' => 'next'
            ],
            'union' => [
                '!' => [
                    2 => ['rel' => null]
                ],
                '<' => [
                    1 => self::WEST,
                    2 => ['rel' => 'prev']
                ],
                '>' => [
                    1 => self::EAST,
                    2 => ['rel' => 'next']
                ]
            ]
        ], $config));
    }

}
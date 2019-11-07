<?php

final class Get extends Request {

    public function __construct(...$lot) {
        $this->type = static::class;
        $this->url = array_shift($lot);
    }

}
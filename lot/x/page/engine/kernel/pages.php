<?php

class Pages extends Files {

    // Inherit to `Files::file()`
    public function file(string $path) {
        return new Page($path);
    }

    public function page(string $path) {
        return $this->file($path);
    }

}
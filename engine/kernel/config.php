<?php

class Config extends Vault {

    public function __construct() {
        $this->bucket = $this->bucket['config'] ?? [];
    }

}
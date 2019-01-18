<?php

// Set global shield ID
if ($id = Config::get('shield')) {
    Shield::$config['id'] = $id;
}
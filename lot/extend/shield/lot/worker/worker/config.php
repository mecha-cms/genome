<?php

// Store shield state to registry…
if ($default = Extend::state('shield', 'default')) {
    // Prioritize default state
    Config::alt(['shield' => $default]);
}

// Set global shield ID
Shield::$id = Config::get('shield');
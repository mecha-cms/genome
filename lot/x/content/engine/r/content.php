<?php

// Set content folder
Content::$state['root'] = CONTENT . DS . $state->name;

// Alias for `Content`
class_alias('Content', 'Skin');
<?php

require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'from.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'to.php';

File::$config['type']['text/yaml'] = 1;

File::$config['x']['yaml'] = 1;
File::$config['x']['yml'] = 1;
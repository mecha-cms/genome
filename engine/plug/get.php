<?php

// Alias(es)
foreach (['cookie', 'get', 'post', 'server', 'session'] as $v) {
    Get::_($v, 'HTTP::' . $v);
}
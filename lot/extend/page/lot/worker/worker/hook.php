<?php

Hook::set('route.exit', function() {
    Message::reset();
}, 20);
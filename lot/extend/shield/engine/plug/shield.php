<?php

Shield::_('message', function(string $kin = "") {
    echo Message::get($kin, false);
});
<?php

// Normalize `$_FILES` value to `$_POST`
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_FILES as $k => $v) {
        foreach ($v as $kk => $vv) {
            if (is_array($vv)) {
                foreach ($vv as $kkk => $vvv) {
                    $_POST[$k][$kkk][$kk] = $vvv;
                }
            } else {
                $_POST[$k][$kk] = $vv;
            }
        }
        $_POST[$k] = new Blob($_POST[$k]);
    }
}
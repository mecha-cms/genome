<?php

class Guardian extends Genome {

    public static function kick($path = "") {
        $url = URL::long($path, false);
        $G = ['source' => $path, 'url' => $url];
        Session::set('url.previous', __url__('current'));
        Hook::fire('guardian.kick.before', [null, $G, $G]);
        header('Location: ' . $url);
        exit;
    }

}
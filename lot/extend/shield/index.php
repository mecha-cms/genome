<?php

Hook::set('on.ready', function() use($config) {
    if (Extend::exist('asset') && $assets = Asset::get()) {
        foreach (Asset::get(null, []) as $k => $v) {
            if (!isset($v[1])) {
                continue;
            }
            foreach ($v[1] as $kk => $vv) {
                // Full path, no change!
                if (strpos($kk, ROOT) === 0 || strpos($kk, '//') === 0 || strpos($kk, '://') !== false) {
                    continue;
                }
                if ($path = File::exist([
                    // Relative to `asset` folder of the current shield
                    SHIELD . DS . $config->shield . DS . 'asset' . DS . $kk,
                    // Relative to `asset` folder of the site
                    ASSET . DS . $kk
                ])) {
                    Asset::reset($kk)->set($path, $vv['stack']);
                }
            }
        }
    }
}, 0);
<?php

$state = Extend::state(__DIR__);

Lot::set([
    'ue' => $state['union'][1][0],
    'ux' => $state['union'][1][3],
    'uid' => uniqid()
], __DIR__);

function fn_block($data) {
    extract(Lot::get(null, [], __DIR__));
    if (!empty($data['content'])) {
        $content = $data['content'];
        // no `[[` character(s) found, skip anyway …
        if (strpos($content, $ue[0]) === false) {
            return $data;
        }
        foreach (Block::get(null, []) as $k => $v) {
            $content = call_user_func($v, $content);
        }
        $data['content'] = str_replace([X . $uid, $uid . X], [$ue[0], $ue[1]], $content);
    }
    return $data;
}

function fn_block_x($data) {
    extract(Lot::get(null, [], __DIR__));
    if (empty($data['content'])) {
        return $data;
    }
    $content = $data['content'];
    if (strpos($content, $ux[0] . $ue[0]) === false) {
        return $data;
    }
    $data['content'] = str_replace([$ux[0] . $ue[0], $ue[1] . $ux[1]], [X . $uid, $uid . X], $content);
    return $data;
}

Hook::set('page.input', 'fn_block_x', 0);
Hook::set('page.input', 'fn_block', 1);
<?php
require_once __DIR__ . "/bootloader.php";
$my = getMe();
if (!$my || !is_admin($my["id"])) {
    showError('管理者アカウントでログインしてください。', 403);
}

function status_localize($is_live_id, $is_started) {
    $pf = '';
    if ($is_started === 0) {
        $pf = ' <small>(not published)</small>';
    }

    switch ($is_live_id) {
        case 0: return 'ended' . $pf;
        case 1: return 'wait pushing' . $pf;
        case 2: return 'started' . $pf;
    }
}

function visibility_localize($id) {
    switch ($id) {
        case 1: return 'public';
        case 2: return 'unlisted';
        case 3: return 'private';
    }
}

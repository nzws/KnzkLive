<?php
require_once("../../../lib/bootloader.php");
$my = getUser($_GET["token"], "opener_token");
if (empty($my)) {
    exit("トークンが見つかりません。 (ERROR:NOT_FOUND_TOKEN)");
}
if ($my["live_current_id"] === 0) {
    exit("OK:IS_NOT_STARTED");
}

$live = getLive($my["live_current_id"]);
if ($live["is_live"] === 0) {
    exit("例外エラー: 予期されない結果が返されました。 (ERROR:FATAL_CURRENT_LIVE_IS_ENDED)");
}

$slot = getSlot($live["slot_id"]);
$rtmp = "rtmp://{$slot["server_ip"]}/live";
$key = "{$live["id"]}stream?token={$live["token"]}";

echo "OK:STARTED#{$rtmp}#{$key}";

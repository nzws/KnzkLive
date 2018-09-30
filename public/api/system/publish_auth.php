<?php
require_once("../../../lib/bootloader.php");

$_GET["tcurl"] = s($_GET["tcurl"]);
if (!$_GET["tcurl"]) { //入力されてない
    http_response_code(404);
    exit();
}
$live = getLive(strstr($_GET["name"], 'stream', true));
if (!$live) { //存在しない
    http_response_code(500);
    exit();
}

if ($live["is_live"] == 0) { //配信終了
    http_response_code(404);
}

if (strstr($_GET["tcurl"], 'token=') !== "token=" . $live["token"]) { //認証
    http_response_code(404);
}
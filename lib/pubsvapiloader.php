<?php
$id = strstr(str_replace('/live/', '', s($_GET["live"])), 'stream', true);
$live = getLive($id);
if (!$live) { //存在しない
    http_response_code(404);
    exit();
}

if ($_GET["authorization"] !== $env["publish_auth"]) {
    http_response_code(404);
    exit();
}

if ($live["is_live"] == 0) { //配信終了
    http_response_code(404);
}
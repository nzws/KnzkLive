<?php
require_once("initload.php");
$_GET["tcurl"] = s($_GET["tcurl"]);
if (!$_GET["tcurl"]) {
    http_response_code(404);
    exit();
}
$live = getLive(strstr($_GET["name"], 'stream', true));
if (!$live) {
    http_response_code(500);
    exit();
}

if (strstr($_GET["tcurl"], 'token=') !== "token=" . $live["token"]) {
    http_response_code(404);
}
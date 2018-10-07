<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/pubsvapiloader.php");

if ($_GET["mode"] === "pre_publish") { //配信開始
    if ($_GET["token"] !== $live["token"]) { //認証
        http_response_code(404);
    }
    setLiveStatus($live["id"], 2);
} elseif ($_GET["mode"] === "done_publish") { //配信終了
    setLiveStatus($live["id"], 1);
}
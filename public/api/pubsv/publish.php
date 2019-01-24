<?php
require_once("../../../lib/bootloader.php");
$id = strstr(str_replace('/live/', '', s($_GET["live"])), 'stream', true);
$live = getLive($id);
if (!$live || $_GET["authorization"] !== $env["publish_auth"] || $live["is_live"] == 0 || $_GET["token"] !== $live["token"]) {
  http_response_code(404);
  exit();
}

if ($_GET["mode"] === "pre_publish") { //配信開始
    setLiveStatus($live["id"], 2);
} elseif ($_GET["mode"] === "done_publish") { //配信終了
    $liveUser = getUser($live["user_id"]);
    if ($liveUser["misc"]["auto_close"] && $live["is_started"] == 1)
      end_live($live["id"]);
    else
      setLiveStatus($live["id"], 1);
}

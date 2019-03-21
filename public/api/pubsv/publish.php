<?php
require_once("../../../lib/bootloader.php");

$key = str_replace('/live/', '', s($_GET["live"]));
$id = strstr($key, 'stream', true);
$live = getLive($id);
if (!$live || $_GET["authorization"] !== $env["publish_auth"] || $live["is_live"] === 0) showError('Authentication failed.', 403);

if (strpos($_GET["live"], 'collabo') !== false) { // collabo
  $collabo_id = strstr(str_replace($id . 'stream', '', $key), 'collabo', true);
  if ($_GET["token"] !== $live["misc"]["collabo_conf"]["token"][$collabo_id]) showError('Authentication failed.', 403);

  if ($_GET["mode"] === "pre_publish") { //配信開始
    setCollaboLiveStatus($collabo_id, $live["id"], 2);
  } elseif ($_GET["mode"] === "done_publish") { //配信終了
    setCollaboLiveStatus($collabo_id, $live["id"], 1);
  }
} else { // main
  if ($_GET["token"] !== $live["token"]) showError('Authentication failed.', 403);

  if ($_GET["mode"] === "pre_publish") { //配信開始
      setLiveStatus($live["id"], 2);
  } elseif ($_GET["mode"] === "done_publish") { //配信終了
      $liveUser = getUser($live["user_id"]);
      if (isset($liveUser["misc"]["auto_close"]) && $liveUser["misc"]["auto_close"] && $live["is_started"] == 1)
        end_live($live["id"]);
      else
        setLiveStatus($live["id"], 1);
  }
}

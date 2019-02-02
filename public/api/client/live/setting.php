<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if ($my["live_current_id"] === 0)
  api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);

$live = getLive($my["live_current_id"]);
if (empty($live))
  api_json(["error" => "エラー: 配信が見つかりません。"]);

if ($_POST["type"] === "sensitive") {
  $live["misc"]["is_sensitive"] = empty($live["misc"]["is_sensitive"]);
  $result = $live["misc"]["is_sensitive"];
} else if ($_POST["type"] === "item") {
  $live["misc"]["able_item"] = empty($live["misc"]["able_item"]);
  $result = $live["misc"]["able_item"];
} else if ($_POST["type"] === "comment") {
  $live["misc"]["able_comment"] = empty($live["misc"]["able_comment"]);
  $result = $live["misc"]["able_comment"];
} else {
  api_json(["error" => "Error: type"]);
}
update_realtime_config($_POST["type"], $result, $live["id"]);

api_json(["success" => setLiveConfig($live["id"], $live["misc"]), "result" => $result]);

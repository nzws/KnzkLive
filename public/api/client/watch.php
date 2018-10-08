<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");
header('Access-Control-Allow-Origin: *');

$live = getLive(s($_GET["id"]));

if (!$live) {
  api_json(["error" => "放送が見つかりません"]);
}

api_json([
  "name" => $live["name"],
  "description" => nl2br($live["description"]),
  "created_at" => $live["created_at"],
  "live_status" => $live["is_live"],
  "viewers_count" => $live["viewers_count"],
  "viewers_max" => $live["viewers_max"],
  "viewers_max_concurrent" => $live["viewers_max_concurrent"]
]);
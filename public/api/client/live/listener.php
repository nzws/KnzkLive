<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if ($my["live_current_id"] === 0)
  api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);

$live = getLive($my["live_current_id"]);
if (empty($live))
  api_json(["error" => "エラー: 配信が見つかりません。"]);

$mysqli = db_start();
$stmt = $mysqli->prepare("SELECT users.* FROM `users_watching` INNER JOIN `users` ON users.id = users_watching.user_id WHERE watch_id = ? AND watching_now = 1;");
$stmt->bind_param("s", $live["id"]);
$stmt->execute();
$row = db_fetch_all($stmt);
$stmt->close();
$mysqli->close();

$users = [];
foreach ($row as $item) {
  $item["misc"] = json_decode($item["misc"], true);
  $users[] = user4Pub($item);
}

api_json($users);

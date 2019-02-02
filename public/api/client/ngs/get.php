<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$live = getLive($_POST["live_id"]);
if (!$live || $live["is_live"] === 0) api_json(["error" => "エラー: 配信が見つかりません。"]);
$liveUser = getUser($live["user_id"]);
if (!$liveUser) api_json(["error" => "エラー: ユーザが見つかりません。"]);

$blocking = get_all_blocking_user($liveUser["id"]);
if ($blocking) {
  $blocking_users = [];
  foreach ($blocking as $item) {
    if (isset($_SESSION["acct"]) && $item["acct"] === $_SESSION["acct"] && $item["is_blocking_watch"] === 1) {
      $blocking_users[] = "#ME#";
    } else {
      $blocking_users[] = $item["acct"];
    }
  }
}

api_json(["w" => base64_encode(json_encode($liveUser["ngwords"])), "u" => $blocking ? base64_encode(json_encode($blocking_users)) : null]);

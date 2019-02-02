<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my || !$my["is_broadcaster"])
  api_json(["error" => "エラー: あなたは配信者ではないか、未ログインです。"]);

if ($_POST["type"] === "remove") {
  $_POST["user_id"] = s($_POST["user_id"]);
  $mysqli = db_start();
  $stmt = $mysqli->prepare("DELETE FROM `users_blocking` WHERE `live_user_id` = ? AND `target_user_id` = ?;");
  $stmt->bind_param("ss", $my["id"], $_POST["user_id"]);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();
} elseif ($_POST["type"] === "add") {
  $acct = getUser(s($_POST["acct"]), "acct");
  if (!$acct) api_json(["error" => "エラー: このユーザーは存在しません。"]);
  if (blocking_user($my["id"], null, $acct["id"])) api_json(["error" => "エラー: このユーザーは追加済みです。"]);
  $permanent = $_POST["is_permanent"] == 1 ? 1 : 0;
  $watch = $_POST["is_blocking_watch"] == 1 ? 1 : 0;

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `users_blocking` (`live_user_id`, `target_user_id`, `created_by`, `is_permanent`, `is_blocking_watch`) VALUES (?, ?, ?, ?, ?);");
  $stmt->bind_param('sssss', $my["id"], $acct["id"], $my["id"], $permanent, $watch);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();
} else {
  api_json(["error" => "typeが不正です。"]);
}
if ($my["live_current_id"] !== 0) update_realtime_config("ngs", null, $my["live_current_id"]);
api_json(["success" => !$err]);

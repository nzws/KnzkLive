<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my || !$my["broadcaster_id"])
  api_json(["error" => "エラー: あなたは配信者ではないか、未ログインです。"]);

$file = getFile($_POST["id"]);
if (!$file || $file["user_id"] !== $my["id"])
  api_json(["error" => "エラー: このファイルは存在しないか、権限がありません。"]);

$delete = deleteFile($file["file_name"], $file["type"]);
if (!$delete) api_json(["error" => "ストレージ削除エラー"]);

$mysqli = db_start();
$stmt = $mysqli->prepare("DELETE FROM `items` WHERE id = ?;");
$stmt->bind_param("s", $file["id"]);
$stmt->execute();
$err = $stmt->error;
$stmt->close();
$mysqli->close();

api_json(["success" => !$err]);

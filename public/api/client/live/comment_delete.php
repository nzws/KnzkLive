<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my) api_json(["error" => "エラー: ログインしてください。"]);
$live = getLive($_POST["live_id"]);
if (!$live || $live["user_id"] !== $my["id"]) api_json(["error" => "エラー: あなたに編集権限がありません。"]);

api_json(["success" => comment_delete($my["id"], $live["user_id"], s($_POST["delete_id"]), $_POST["is_knzklive"] == 1)]);

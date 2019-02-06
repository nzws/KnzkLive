<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if ($my["live_current_id"] === 0)
  api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);

api_json(["success" => comment_delete($my["id"], $my["live_current_id"], s($_POST["delete_id"]), $_POST["is_knzklive"] == 1)]);

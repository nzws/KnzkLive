<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");
$my = getMe();

if (!$my) {
  api_json(["error" => "エラー: ログインしてください。"]);
}

$err = comment_post($_POST["content"], $my["id"], $_POST["live_id"]);

if (!is_numeric($err)) {
  api_json(["error" => $err]);
}

api_json(["ok" => true]);
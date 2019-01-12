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

if ($_SESSION["account_provider"] === "twitter" && $_POST["is_local"] == 0) {
  $connection = new \Abraham\TwitterOAuth\TwitterOAuth($env["tw_login"]["key"], $env["tw_login"]["secret"], $_SESSION["token"], $_SESSION["token_secret"]);
  $statues = $connection->post("statuses/update", ["status" => $_POST["content_tw"]]);
}

api_json(["ok" => true]);

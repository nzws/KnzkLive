<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");

$my = getMe();
if (!$my) api_json(["error" => "エラー: ログインしてください。"]);

$live = getLive($_POST["live_id"]);
if (!$live) api_json(["error" => "エラー: 配信が見つかりません"]);
if (!$live["misc"]["able_comment"] || blocking_user($live["user_id"], $_SERVER["REMOTE_ADDR"], $my["acct"])) api_json(["error" => "エラー: コメントは現在使用できません。"]);

$err = comment_post($_POST["content"], $my["id"], $_POST["live_id"]);

if (!is_numeric($err)) {
  api_json(["error" => $err]);
}

if ($_SESSION["account_provider"] === "twitter" && $_POST["is_local"] == 0) {
  $connection = new \Abraham\TwitterOAuth\TwitterOAuth($env["tw_login"]["key"], $env["tw_login"]["secret"], $_SESSION["token"], $_SESSION["token_secret"]);
  $statues = $connection->post("statuses/update", ["status" => $_POST["content_tw"]]);
}

api_json(["ok" => true]);

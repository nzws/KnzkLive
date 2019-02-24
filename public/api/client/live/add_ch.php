<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if ($my["live_current_id"] === 0)
  api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);

$live = getLive($my["live_current_id"]);
if (empty($live))
  api_json(["error" => "エラー: 配信が見つかりません。"]);

$user = getUser($_POST["acct"], "acct");
if (empty($user))
  api_json(["error" => "エラー: ユーザーが見つかりません。"]);

api_json(["success" => add_donate($live["id"], $user["id"], s($_POST["amount"]), s($_POST["currency"]))]);

<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();

$live = getLive(s($_POST["live_id"]));
if (empty($live))
  api_json(["error" => "エラー: 配信が見つかりません。"]);

if ($my["live_current_id"] !== $live["id"] && !is_admin($my["id"]))
  api_json(["error" => "エラー: あなたは現在配信していないか、編集権限がありません。"]);

if (!isset($live["misc"]["collabo"])) $live["misc"]["collabo"] = [];

if (isset($_POST["type"])) {
  if ($_POST["type"] === "add") {
    if (count($live["misc"]["collabo"]) > 3) api_json(["error" => "エラー: コラボレータに登録できるのは3人までです。"]);

    $user = getUser($_POST["user_acct"], "acct");
    if (!$user) api_json(["error" => "エラー: ユーザが見つかりません。\n* 合ってるのにも関わらず表示される場合は、相手にKnzkLiveに一度ログインしてもらってください。"]);

    $live["misc"]["collabo"][] = $user["id"];
  } else {
    $user = getUser($_POST["user_id"]);
    if (!$user) api_json(["error" => "エラー: ユーザが見つかりません。"]);

    $live["misc"]["collabo"] = array_values(array_diff($live["misc"]["collabo"], [$user["id"]]));
  }

  api_json(["success" => setLiveConfig($live["id"], $live["misc"])]);
} else {
  $user = [];
  foreach ($live["misc"]["collabo"] as $user_id) {
    $user[] = user4Pub(getUser($user_id));
  }

  api_json($user);
}

<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");
$my = getMe();
if (!$my) {
  api_json(["error" => "エラー: ログインしてください。"]);
}
$live = getLive($_POST["live_id"]);
if (!$live) api_json(["error" => "エラー: 配信が見つかりません"]);


if ($_POST["type"] === "emoji") {
  $liveUser = getUser($live["user_id"]);
  $emojis = [
    "👍" => "https://twemoji.maxcdn.com/2/svg/1f44d.svg",
    "❤️" => "https://twemoji.maxcdn.com/2/svg/2764.svg",
    "👏️" => "https://twemoji.maxcdn.com/2/svg/1f44f.svg",
    "🎉️" => "https://twemoji.maxcdn.com/2/svg/1f389.svg",
    "liver" => $liveUser["misc"]["avatar"],
    "me" => $my["misc"]["avatar"]
  ];
  if (!is_numeric($_POST["count"]) || $_POST["count"] < 1 || $_POST["count"] > 100)
    api_json(["error" => "エラー: 個数が不正です。"]);
  if (array_search($_POST["dir"], ["left-to-right", "right-to-left", "top-to-bottom", "bottom-to-top"]) === false)
    api_json(["error" => "エラー: 方向が不正です。"]);
  if (array_search($_POST["emoji"], ["👍", "❤️", "👏️", "🎉️", "liver", "me"]) === false)
    api_json(["error" => "エラー: 絵文字が不正です。"]);

  $point = (intval($_POST["count"]) * 5) + ($_POST["spin"] == 1 ? 50 : 0);
} else {
  api_json(["error" => "エラー: このアイテムは存在しないか受付停止中です。"]);
}

if (empty($point) || intval($point) !== $point || $point < 0)
  api_json(["error" => "内部エラー: ポイント計算に失敗しました。管理者にお問い合わせください。"]);
if (!check_point_true($my["point_count"], $point)) api_json(["error" => "エラー: 残高が足りません。"]);

if ($_POST["confirm"] != 1) api_json(["confirm" => true, "point" => $point]); //サーバー側で消費ポイントを再計算して確認させる

if ($_POST["type"] === "emoji") {
  $desc = s($_POST["emoji"]) . "絵文字" . s($_POST["count"]);
  $data = [
    "repeat_html" => "<img src='{$emojis[$_POST["emoji"]]}'/>",
    "repeat_num" => ($_POST["count"] < 6 ? $_POST["count"] : 6),
    "class" => ($_POST["spin"] == 1 ? "spin " : "") . $_POST["dir"],
  ];
  for ($i = 0; $i < intval(ceil($_POST["count"] / 6)); $i++) {
    $data["style"] = ($_POST["dir"] === "left-to-right" || $_POST["dir"] === "right-to-left" ? "top" : "left") . ": " . rand(2, 98) . "%;animation-delay:" . rand(1, 2000) . "ms";
    send_item($data, $live["id"], "emoji");
  }
}
$n = add_point($my["id"], $point * -1, "item", $_POST["type"]);
comment_post("<div class=\"alert alert-primary\">{$desc} を投下しました！</div>", $my["id"], $live["id"], true);
api_json(["success" => $n]);


function send_item($item, $live_id, $type) {
  global $env;

  $data = [
    "type" => "item",
    "item_type" => $type,
    "live_id" => $live_id,
    "item" => $item
  ];

  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents($env["websocket_url"]."/send_prop", false, $options);
  if ($contents === false) api_json(["error" => "エラー: リアルタイム通信サーバーが応答しなかったため、中断されました。"]);
}

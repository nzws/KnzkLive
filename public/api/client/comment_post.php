<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");
$my = getMe();

if (!$my) {
  api_json(["error" => "エラー: ログインしてください。"]);
}

$content = "<p>" . nl2br(s($_POST["content"])) . "</p>";

$err = comment_post($content, $my["id"], $_POST["live_id"]);

if (!is_numeric($err)) {
  api_json(["error" => $err]);
}

$data = [
  "id" => "knzklive_".$err,
  "live_id" => s($_POST["live_id"]),
  "is_knzklive" => true,
  "account" => [
    "display_name" => $my["name"],
    "acct" => $my["acct"]." (local)",
    "username" => $my["acct"]." (local)",
    "avatar" => $my["misc"]["avatar"]
  ],
  "content" => $content,
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
$contents = file_get_contents($env["websocket_url"]."/send_comment", false, $options);

api_json(["ok" => true]);
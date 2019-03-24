<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();

$live = getLive(s($_POST["live_id"]));
if (empty($live)) {
    api_json(["error" => "エラー: 配信が見つかりません。"]);
}

if ($my["live_current_id"] !== $live["id"] && !is_admin($my["id"]) && !is_collabo($my["id"], $live["id"])) {
    api_json(["error" => "エラー: あなたは現在配信していないか、編集権限がありません。"]);
}

$force = $_POST["force"] === "1";

if ($_POST["type"] === "sensitive") {
    $live["misc"]["is_sensitive"] = $force ? true : empty($live["misc"]["is_sensitive"]);
    $result = $live["misc"]["is_sensitive"];
} elseif ($_POST["type"] === "item") {
    $live["misc"]["able_item"] = $force ? false : empty($live["misc"]["able_item"]);
    $result = $live["misc"]["able_item"];
} elseif ($_POST["type"] === "comment") {
    $live["misc"]["able_comment"] = $force ? false : empty($live["misc"]["able_comment"]);
    $result = $live["misc"]["able_comment"];
} elseif ($_POST["type"] === "stop") {
    end_live($live["id"]);
} else {
    api_json(["error" => "Error: type"]);
}
update_realtime_config($_POST["type"], $result, $live["id"]);
$success = $_POST["type"] === "stop" ? true : setLiveConfig($live["id"], $live["misc"]);

if ($force) {
    $data = [
    "embeds" => [
      [
        "title" => "サービスモデレータの操作",
        "url" => liveUrl($live["id"]),
        "color" => "16761095",
        "author" => [
          "name" => $my["name"] . " (" . $my["acct"] . ")",
          "url" => $my["misc"]["user_url"],
          "icon_url" => $my["misc"]["avatar"]
        ],
        "fields" => [
          [
            "name" => "Broadcast ID",
            "value" => $live["id"]
          ],
          [
            "name" => "IP",
            "value" => $_SERVER["REMOTE_ADDR"]
          ],
          [
            "name" => "Type",
            "value" => $_POST["type"]
          ]
        ]
      ]
    ]
  ];
    sendToDiscord($data);
}

api_json(["success" => $success, "result" => $result]);

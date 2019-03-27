<?php
require_once "../../../lib/bootloader.php";
require_once "../../../lib/apiloader.php";

$my = getMe();

if ($my["live_current_id"] == 0) {
    api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);
}

$live_id = s($my["live_current_id"]);
$title = s($_POST["name"]);
$desc = s($_POST["description"]);

if (!$title || !$desc) {
    api_json(["error" => "エラー: 必要な値が入力されていません。"]);
}

$mysqli = db_start();
$stmt = $mysqli->prepare("UPDATE `live` SET name = ?, description = ? WHERE id = ?;");
$stmt->bind_param('sss', $title, $desc, $live_id);
$err = $stmt->error;
$stmt->execute();
$stmt->close();
$mysqli->close();
if ($err) {
    api_json(["error" => "エラー: 登録中に不明なエラーが発生しました。文字数制限に引っかかっているかサーバーエラーの可能性があります。"]);
} else {
    api_json(live4Pub(getLive($live_id)));
}

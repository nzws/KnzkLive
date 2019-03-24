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

if (!empty($live["misc"]["collabo"][$my["id"]]["slot"])) {
    api_json(["error" => "エラー: 配信枠は取得済みです。"]);
}

$slot = getAbleSlot();
if (!$slot) {
    api_json(["error" => "エラー: 現在、配信枠が不足しています。"]);
}

setSlot($slot, 1);
$live["misc"]["collabo"][$my["id"]]["slot"] = $slot;

api_json(["success" => setLiveConfig($live["id"], $live["misc"])]);

<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();

$live = getLive(s($_POST["live_id"]));
if (empty($live)) {
    api_json(["error" => "エラー: 配信が見つかりません。"]);
}

if ($my["live_current_id"] !== $live["id"] && !is_admin($my["id"])) {
    api_json(["error" => "エラー: あなたは現在配信していないか、編集権限がありません。"]);
}

if (!isset($live["misc"]["collabo"])) {
    $live["misc"]["collabo"] = [];
}

if (isset($_POST["type"])) {
    if ($_POST["type"] === "add") {
        if (count($live["misc"]["collabo"]) > 2) {
            api_json(["error" => "エラー: コラボレータに登録できるのは3人までです。"]);
        }

        $user = getUser($_POST["user_acct"], "acct");
        if (!$user) {
            api_json(["error" => "エラー: ユーザが見つかりません。\n* 合ってるのにも関わらず表示される場合は、相手にKnzkLiveに一度ログインしてもらってください。"]);
        }
        if ($user["id"] === $my["id"]) {
            api_json(["error" => "エラー: 自分自身は追加できません。"]);
        }
        if (is_collabo($user["id"], $live["id"])) {
            api_json(["error" => "エラー: 追加済みです。"]);
        }

        $live["misc"]["collabo"][$user["id"]] = [
      "token" => generateHash(),
      "status" => 1
    ];
    } else {
        $user = getUser($_POST["user_id"]);
        if (!$user) {
            api_json(["error" => "エラー: ユーザが見つかりません。"]);
        }

        setSlot($live["misc"]["collabo"][$user["id"]]["slot"], 0);
        setCollaboLiveStatus($user["id"], $live["id"], 1);
        if ($live["misc"]["collabo"][$user["id"]]["status"] === 2) {
            disconnectClient($live["id"], $user["id"]);
        }
        unset($live["misc"]["collabo"][$user["id"]]);
    }
    update_realtime_config("update_collabo", $user["id"], $live["id"]);
    api_json(["success" => setLiveConfig($live["id"], $live["misc"])]);
} else {
    $user = [];
    foreach ($live["misc"]["collabo"] as $user_id => $data) {
        $user[] = user4Pub(getUser($user_id));
    }

    api_json($user);
}

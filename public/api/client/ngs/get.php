<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$live = getLive($_POST["live_id"]);
if (!$live) {
    api_json(["error" => "エラー: 配信が見つかりません。"]);
}
$liveUser = getUser($live["user_id"]);
if (!$liveUser) {
    api_json(["error" => "エラー: ユーザが見つかりません。"]);
}

$blocking = get_all_blocking_user($liveUser["id"]);
if ($blocking) {
    $blocking_users = [];
    foreach ($blocking as $item) {
        if (isset($_SESSION["acct"]) && $item["target_user_acct"] === $_SESSION["acct"] && $item["is_blocking_watch"] === 1) {
            $blocking_users[] = "#ME#";
        } else {
            $blocking_users[] = $item["target_user_acct"];
        }
    }
}

$donator = get_donate($live["id"]);
if ($donator) {
    $i = 0;
    while (isset($donator[$i])) {
        $donator[$i]["account"] = user4Pub(getUser($donator[$i]["user_id"]));
        $donator[$i]["ended_at"] = dateHelper($donator[$i]["ended_at"]);
        ++$i;
    }
}

$emoji = getEmojis($liveUser["id"], "comment");

api_json([
    "w" => base64_encode(json_encode($liveUser["ngwords"])),
    "u" => $blocking ? base64_encode(json_encode($blocking_users)) : null,
    "p" => base64_encode(json_encode(get_comment_deleted_list($live["id"]))),
    "donator" => $donator ? $donator : null,
    "emojis" => $emoji ? $emoji : null
]);

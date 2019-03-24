<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my) {
    api_json(["error" => "エラー: ログインしてください。"]);
}

if (isset($_POST["live_id"])) {
    $live = getLive($_POST["live_id"]);
    if (!$live && !is_admin($my["id"]) && !is_collabo($my["id"], $live["id"])) {
        api_json(["error" => "エラー: あなたに編集権限がありません。"]);
    }
    $created_by = $my["id"];
    $my["id"] = $live["user_id"];
} elseif (!$my["broadcaster_id"]) {
    api_json(["error" => "エラー: あなたは配信者ではありません。"]);
}

if ($_POST["type"] === "remove") {
    $_POST["user_id"] = s($_POST["user_id"]);
    $mysqli = db_start();
    $stmt = $mysqli->prepare("DELETE FROM `users_blocking` WHERE `live_user_id` = ? AND `target_user_acct` = ?;");
    $stmt->bind_param("ss", $my["id"], $_POST["user_id"]);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
} elseif ($_POST["type"] === "add") {
    $acct = s($_POST["acct"]);
    if (!$acct) {
        api_json(["error" => "エラー: このユーザーは存在しません。"]);
    }
    if (blocking_user($my["id"], null, $acct)) {
        api_json(["error" => "エラー: このユーザーは追加済みです。"]);
    }
    $permanent = $_POST["is_permanent"] == 1 ? 1 : 0;
    $watch = $_POST["is_blocking_watch"] == 1 ? 1 : 0;

    $mysqli = db_start();
    if (isset($created_by)) {
        $misc = "by " . $created_by;
        $stmt = $mysqli->prepare("INSERT INTO `users_blocking` (`live_user_id`, `target_user_acct`, `created_by`, `is_permanent`, `is_blocking_watch`, `misc`) VALUES (?, ?, ?, ?, ?, ?);");
        $stmt->bind_param('ssssss', $my["id"], $acct, $my["id"], $permanent, $watch, $misc);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO `users_blocking` (`live_user_id`, `target_user_acct`, `created_by`, `is_permanent`, `is_blocking_watch`) VALUES (?, ?, ?, ?, ?);");
        $stmt->bind_param('sssss', $my["id"], $acct, $my["id"], $permanent, $watch);
    }
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
} else {
    api_json(["error" => "typeが不正です。"]);
}
if ($my["live_current_id"] !== 0 || (isset($live) && $live["is_live"] !== 0)) {
    update_realtime_config("ngs", null, isset($live) ? $live["id"] : $my["live_current_id"]);
}
api_json(["success" => !$err, "error" => ($env["is_testing"] && !empty($err) ? $err : null)]);

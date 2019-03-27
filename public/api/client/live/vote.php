<?php
require_once "../../../../lib/bootloader.php";
require_once "../../../../lib/apiloader.php";

$my = getMe();
if ($my["live_current_id"] === 0) {
    api_json(["error" => "エラー: あなたは現在配信していないか、配信権限がありません。"]);
}

$live = getLive($my["live_current_id"]);
if (empty($live)) {
    api_json(["error" => "エラー: 配信が見つかりません。"]);
}
$vote = loadVote($live["id"]);

if (empty($vote) && !isset($_POST["end"])) {
    $_SESSION["prop_vote_is_post"] = $_POST["is_post"] == 0;
    $result = createVote($live["id"], $_POST["title"], [
        $_POST["vote1"], $_POST["vote2"], $_POST["vote3"], $_POST["vote4"]
    ], liveTag($live), $my["id"]);
    api_json(["success" => $result]);
} elseif (isset($_POST["end"])) {
    api_json(["success" => endVote($live["id"], liveTag($live), $my["id"])]);
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

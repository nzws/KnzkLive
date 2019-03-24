<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my || !$my["broadcaster_id"]) {
    api_json(["error" => "エラー: あなたは配信者ではないか、未ログインです。"]);
}

if ($_POST["type"] === "remove") {
    $my["ngwords"] = array_diff($my["ngwords"], [$_POST["word"]]);
    $my["ngwords"] = array_values($my["ngwords"]);
} elseif ($_POST["type"] === "add") {
    $word = s($_POST["word"]);

    if (array_search($word, $my["ngwords"]) !== false) {
        api_json(["error" => "エラー: このワードは既に追加されています。"]);
    }

    array_unshift($my["ngwords"], $word);
} else {
    api_json(["error" => "typeが不正です。"]);
}
if ($my["live_current_id"] !== 0) {
    update_realtime_config("ngs", null, $my["live_current_id"]);
}
api_json(["success" => setNgWords($my["ngwords"], $my["id"]), "word" => isset($word) ? $word : null]);

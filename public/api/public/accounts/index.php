<?php
$allow_nologin = false;
require_once "../../../../lib/api_public_loader.php";

if (isset($_GET["user_id"])) {
    $user = s($_GET["user_id"]);
    $type = "id";
} elseif (isset($_GET["user_acct"])) {
    $user = s($_GET["user_acct"]);
    $type = "acct";
} elseif (isset($_GET["broadcaster_id"])) {
    $user = s($_GET["broadcaster_id"]);
    $type = "broadcaster_id";
} else {
    // 自分
    $user = $my["id"];
    $type = "id";
}

$user = getUser($id, $type);

if (!$user) {
    return_api_error("このユーザは存在しません。", 404);
}

api_json(user4Pub($user));

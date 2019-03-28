<?php
$allow_nologin = true;
require_once "../../../../lib/api_public_loader.php";

if (empty($_GET["type"])) {
    return_api_error("type は必須です。", 400);
} elseif ($_GET["type"] === "last_stream") {
    $list = getLastLives((int) $_GET["page"], (int) $_GET["per_page"]);
    $type = "accts";
} else {
    return_api_error("この type は存在しません。", 400);
}

if (empty($list) || $list === false) {
    return_api_error("値が間違っています。", 400);
}

if ($type === "accts") {
    $list = array_acct_public($list);
}

api_json($list);
